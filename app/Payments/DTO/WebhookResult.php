<?php

namespace App\Payments\DTO;

/**
 * Normalised outcome of verifying a gateway webhook.
 */
final class WebhookResult
{
    public function __construct(
        public readonly bool $verified,
        public readonly ?int $registrationId = null,
        public readonly ?string $reference = null,
        public readonly ?float $amount = null,
        public readonly string $status = 'failed',   // paid | failed
        /** @var array<string,mixed> Raw payload for audit. */
        public readonly array $raw = [],
    ) {}

    public static function failed(array $raw = []): self
    {
        return new self(verified: false, status: 'failed', raw: $raw);
    }

    public function isPaid(): bool
    {
        return $this->verified && $this->status === 'paid';
    }
}
