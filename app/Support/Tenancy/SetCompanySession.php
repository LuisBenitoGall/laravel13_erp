<?php

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Fija la empresa de sesión tras validar pertenencia a company activa matriz (R-TEN-03).
 * Las Delegaciones (`parent_id` no null) no se pueden fijar como empresa de sesión.
 */
final class SetCompanySession
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, User $user, int $companyId): void
    {
        if (! $user->belongsToSelectableCompany($companyId)) {
            throw new RuntimeException('User does not belong to the requested active matrix company.');
        }

        $request->session()->put('current_company_id', $companyId);
        $this->currentCompany->set($companyId);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget('current_company_id');
        $this->currentCompany->set(null);
    }
}
