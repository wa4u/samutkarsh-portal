<?php

namespace App\Payments\Gateways;

use App\Models\Registration;
use App\Models\Setting;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;
use RuntimeException;

abstract class AbstractGateway implements PaymentGateway
{
    /**
     * Read this gateway's config. An admin-editable Setting
     * `payment.{key}.{name}` wins when present; otherwise we fall back to
     * config()/.env. This is what makes the UPI ID, payee, fee, and on/off
     * flags editable in admin without touching .env.
     */
    protected function config(string $name, mixed $default = null): mixed
    {
        $setting = Setting::get("payment.{$this->key()}.{$name}");
        if ($setting !== null && $setting !== '') {
            return $setting;
        }

        return config("payments.config.{$this->key()}.{$name}", $default);
    }

    public function isEnabled(): bool
    {
        // filter_var handles both stored strings ("1"/"0") and real booleans.
        return filter_var($this->config('enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function isManual(): bool
    {
        return false;
    }

    /** The fee for an admission seat: per-registration amount, else the admin-set fallback, else config. */
    protected function amountFor(Registration $registration): float
    {
        $fallback = Setting::get('payment.admission_fee');
        $fallback = ($fallback !== null && $fallback !== '') ? $fallback : config('payments.admission_fee', 0);

        return (float) ($registration->payment_amount ?? $fallback);
    }

    /** Manual gateways have no webhook; override in online gateways. */
    public function verifyWebhook(Request $request): WebhookResult
    {
        throw new RuntimeException("Gateway [{$this->key()}] does not support webhooks.");
    }
}
