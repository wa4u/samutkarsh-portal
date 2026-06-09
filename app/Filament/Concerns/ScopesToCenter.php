<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Containerises a Filament Resource to the logged-in Center Head's center.
 *
 * Apply to any Resource whose model has a `center_id` column. It enforces
 * containment in TWO places so a Center Head can never read OR write outside
 * their center, even by tampering with form payloads or guessing record IDs:
 *
 *   1. getEloquentQuery() — filters every list/view/edit/delete query. Because
 *      Filament resolves the edit/view/delete record THROUGH this query, a Head
 *      gets a 404 on another center's record rather than silent data exposure.
 *   2. mutateFormDataToServer() hooks (used by the resource's Create/Edit pages)
 *      — force `center_id` to the Head's own center on write.
 *
 * Trust Admin and Education Council are global and bypass the filter entirely.
 */
trait ScopesToCenter
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->isCenterHead() && $user->center_id) {
            $query->where($query->getModel()->getTable() . '.center_id', $user->center_id);
        }

        return $query;
    }

    /**
     * Call from the resource's CreateRecord / EditRecord page in
     * mutateFormDataBeforeCreate() / mutateFormDataBeforeSave().
     */
    public static function enforceCenterId(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->isCenterHead() && $user->center_id) {
            $data['center_id'] = $user->center_id;
        }

        return $data;
    }
}
