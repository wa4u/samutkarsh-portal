<?php

namespace App\Payments\Gateways;

use App\Models\Registration;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;
use RuntimeException;

abstract class AbstractGateway implements PaymentGateway
{
    /** Read this gateway's config block: config("payments.config.{key}.{name}"). */
    protected function config(string $name, mixed $default = null): mixed
    {
        return config("payments.config.{$this->key()}.{$name}", $default);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config('enabled', false);
    }

    public function isManual(): bool
    {
        return false;
    }

    /** The fee charged for an admission seat, resolved per-registration or from config. */
    protected function amountFor(Registration $registration): float
    {
        return (float) ($registration->payment_amount ?? config('payments.admission_fee', 0));
    }

    /** Manual gateways have no webhook; override in online gateways. */
    public function verifyWebhook(Request $request): WebhookResult
    {
        throw new RuntimeException("Gateway [{$this->key()}] does not support webhooks.");
    }
}
