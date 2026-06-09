<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Payments\PaymentManager;
use App\Services\AdmissionPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $payments,
        protected AdmissionPaymentService $admissions,
    ) {}

    /**
     * Single inbound endpoint for every online gateway: POST /payments/webhook/{gateway}.
     * The matching driver verifies the signature; on a verified 'paid' event we
     * confirm the seat. Always returns 2xx on a handled request so the gateway
     * stops retrying — failures are logged, not surfaced to the caller.
     */
    public function handle(Request $request, string $gateway): JsonResponse
    {
        if (! $this->payments->has($gateway)) {
            return response()->json(['message' => 'unknown gateway'], 404);
        }

        $result = $this->payments->gateway($gateway)->verifyWebhook($request);

        if (! $result->verified) {
            Log::warning('payment.webhook.rejected', ['gateway' => $gateway]);
            return response()->json(['message' => 'invalid signature'], 400);
        }

        if (! $result->isPaid()) {
            // Verified but not a success event (e.g. failed/authorized) — ack only.
            return response()->json(['message' => 'acknowledged']);
        }

        $registration = Registration::find($result->registrationId);

        if (! $registration) {
            Log::error('payment.webhook.registration_missing', [
                'gateway' => $gateway, 'registration_id' => $result->registrationId,
            ]);
            // 200 so the gateway does not hammer us; nothing actionable on retry.
            return response()->json(['message' => 'registration not found']);
        }

        $this->admissions->confirm(
            registration: $registration,
            gateway: $gateway,
            amount: $result->amount ?? (float) $registration->payment_amount,
            reference: $result->reference,
            meta: $result->raw,
        );

        return response()->json(['message' => 'ok']);
    }
}
