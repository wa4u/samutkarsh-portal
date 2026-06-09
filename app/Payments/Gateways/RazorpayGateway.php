<?php

namespace App\Payments\Gateways;

use App\Models\Registration;
use App\Payments\DTO\PaymentIntent;
use App\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Razorpay online checkout. Reference implementation of an auto-confirming
 * gateway: createIntent() opens a Razorpay Order; the verified `payment.captured`
 * / `order.paid` webhook confirms the seat. A Cashfree/Stripe driver would be a
 * near-identical class — that is the modularity payoff.
 */
class RazorpayGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'razorpay';
    }

    public function label(): string
    {
        return 'Razorpay (Online)';
    }

    public function createIntent(Registration $registration): PaymentIntent
    {
        $amount = $this->amountFor($registration);

        $response = Http::withBasicAuth($this->config('key_id'), $this->config('key_secret'))
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount'   => (int) round($amount * 100),   // paise
                'currency' => 'INR',
                'receipt'  => 'REG' . $registration->id,
                'notes'    => ['registration_id' => (string) $registration->id],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Razorpay order creation failed: ' . $response->body());
        }

        $order = $response->json();

        return new PaymentIntent(
            gateway: $this->key(),
            type: 'redirect',
            amount: $amount,
            reference: $order['id'] ?? null,
            payload: [
                // Consumed by Razorpay checkout.js on the frontend.
                'key'      => $this->config('key_id'),
                'order_id' => $order['id'] ?? null,
                'amount'   => $order['amount'] ?? (int) round($amount * 100),
                'currency' => 'INR',
                'name'     => config('app.name'),
            ],
        );
    }

    public function verifyWebhook(Request $request): WebhookResult
    {
        $raw = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');
        $expected = hash_hmac('sha256', $raw, (string) $this->config('webhook_secret'));

        // Constant-time comparison — never a loose == on signatures.
        if (! $signature || ! hash_equals($expected, $signature)) {
            return WebhookResult::failed(['reason' => 'signature_mismatch']);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;

        if (! in_array($event, ['payment.captured', 'order.paid'], true)) {
            // Verified but not a terminal success event — acknowledge, do nothing.
            return new WebhookResult(verified: true, status: 'failed', raw: $payload);
        }

        $entity = data_get($payload, 'payload.payment.entity', []);
        $registrationId = data_get($entity, 'notes.registration_id');

        return new WebhookResult(
            verified: true,
            registrationId: $registrationId ? (int) $registrationId : null,
            reference: $entity['id'] ?? null,                  // pay_xxx
            amount: isset($entity['amount']) ? $entity['amount'] / 100 : null,
            status: 'paid',
            raw: $payload,
        );
    }
}
