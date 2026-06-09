<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksCenterOwnership;

/**
 * Payments are an immutable audit record: created only by AdmissionPaymentService,
 * never edited or deleted from the panel. Hence create/update/delete are denied
 * to everyone (Trust Admin's Gate::before bypass is intentionally not in play for
 * a resource that simply has no create/edit pages — but we deny here too for
 * defense in depth against programmatic authorize() calls).
 */
class PaymentPolicy
{
    use ChecksCenterOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_payment');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('view_payment') && $this->ownsCenter($user, $payment->center_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Payment $payment): bool
    {
        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }
}
