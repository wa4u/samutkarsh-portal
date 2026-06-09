<?php

namespace App\Payments\Contracts;

use App\Models\Registration;
use App\Payments\DTO\PaymentIntent;
use App\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;

/**
 * Contract every payment module implements. To add a gateway:
 *   1. Create a class implementing this interface (extend AbstractGateway).
 *   2. Register it in config/payments.php under `gateways`.
 *   3. Toggle it on via its `enabled` config / env flag.
 * No core code changes required — that is the whole point of the module system.
 */
interface PaymentGateway
{
    /** Stable machine key, e.g. "razorpay". Used in routes, config, and the payments table. */
    public function key(): string;

    /** Human label shown in the UI, e.g. "Razorpay (Online)". */
    public function label(): string;

    /** Is this gateway switched on? (driven by config/env, never hard-coded). */
    public function isEnabled(): bool;

    /**
     * Manual gateways (cash, UPI-QR) are confirmed by an admin, not a webhook.
     * Online gateways (Razorpay) return false and rely on verifyWebhook().
     */
    public function isManual(): bool;

    /**
     * Begin a payment for a registration. Returns instructions for the client:
     * a redirect/checkout payload (online), a QR + UPI string (UPI), or a
     * manual placeholder (cash).
     */
    public function createIntent(Registration $registration): PaymentIntent;

    /**
     * Verify an inbound webhook and extract the result. Online gateways MUST
     * verify the signature here. Manual gateways throw / return ->failed().
     */
    public function verifyWebhook(Request $request): WebhookResult;
}
