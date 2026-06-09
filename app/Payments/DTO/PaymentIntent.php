<?php

namespace App\Payments\DTO;

/**
 * Immutable description of how the client should complete a payment.
 *
 * `type` drives the frontend:
 *   - 'redirect' : send the user to `payload['url']` (or open checkout with payload)
 *   - 'qr'       : render `payload['qr_data_uri']`; user pays via UPI app
 *   - 'manual'   : nothing to show online; an admin records the payment
 */
final class PaymentIntent
{
    public function __construct(
        public readonly string $gateway,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $currency = 'INR',
        public readonly ?string $reference = null,
        /** @var array<string,mixed> Gateway-specific data (checkout keys, qr_data_uri, upi_string…). */
        public readonly array $payload = [],
        public readonly ?string $instructions = null,
    ) {}

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'gateway'      => $this->gateway,
            'type'         => $this->type,
            'amount'       => $this->amount,
            'currency'     => $this->currency,
            'reference'    => $this->reference,
            'payload'      => $this->payload,
            'instructions' => $this->instructions,
        ];
    }
}
