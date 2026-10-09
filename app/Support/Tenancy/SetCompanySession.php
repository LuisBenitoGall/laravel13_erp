<?php

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Fija la empresa de sesión tras validar pertenencia a company activa (R-TEN-03).
 */
final class SetCompanySession
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, User $user, int $companyId): void
    {
        if (! $user->belongsToCompany($companyId)) {
            throw new RuntimeException('User does not belong to the requested active company.');
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
