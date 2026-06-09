<?php

namespace App\Payments\Gateways;

use App\Models\Registration;
use App\Payments\DTO\PaymentIntent;

/**
 * Cash collected at the center. There is nothing for the student to do online;
 * a Center Head / admin records the receipt via the Filament "Record Payment"
 * action, which routes through AdmissionPaymentService just like any webhook.
 */
class CashGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'cash';
    }

    public function label(): string
    {
        return 'Cash (at center)';
    }

    public function isManual(): bool
    {
        return true;
    }

    public function createIntent(Registration $registration): PaymentIntent
    {
        return new PaymentIntent(
            gateway: $this->key(),
            type: 'manual',
            amount: $this->amountFor($registration),
            instructions: 'Pay in cash at the center; the office will mark your seat confirmed.',
        );
    }
}
