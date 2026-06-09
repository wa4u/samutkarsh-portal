<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

/**
 * The ONE place a registration becomes 'admitted'. Every payment path — Razorpay
 * webhook, UPI-UTR confirmation, cash entry — funnels through confirm(), so the
 * money-in → seat-confirmed transition has a single, auditable definition.
 */
class AdmissionPaymentService
{
    /**
     * Record a successful payment and admit the registration.
     *
     * Idempotent: a repeated (gateway, reference) — e.g. a webhook retry — returns
     * the existing Payment without double-admitting or duplicating the row.
     */
    public function confirm(
        Registration $registration,
        string $gateway,
        float $amount,
        ?string $reference = null,
        array $meta = [],
        ?int $recordedBy = null,
    ): Payment {
        return DB::transaction(function () use ($registration, $gateway, $amount, $reference, $meta, $recordedBy) {
            // Idempotency guard for gateways that supply a reference (webhooks/UPI).
            if ($reference !== null) {
                $existing = Payment::where('gateway', $gateway)
                    ->where('reference', $reference)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $payment = Payment::create([
                'registration_id' => $registration->id,
                'center_id'       => $registration->center_id,
                'gateway'         => $gateway,
                'amount'          => $amount,
                'currency'        => 'INR',
                'status'          => 'paid',
                'reference'       => $reference,
                'meta'            => $meta ?: null,
                'recorded_by'     => $recordedBy,
                'paid_at'         => now(),
            ]);

            // Mirror the canonical payment fields onto the registration and admit it.
            $registration->forceFill([
                'status'            => 'admitted',
                'payment_reference' => $reference,
                'payment_amount'    => $amount,
                'payment_status'    => 'paid',
                'paid_at'           => now(),
            ])->save();

            return $payment;
        });
    }
}
