<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;

/**
 * Readiness computada de onboarding (company de sesión): nif + logo + ≥1 workplace Active.
 */
class CompanySetupReadiness
{
    /**
     * @return array{
     *     complete: bool,
     *     completed_steps: int,
     *     total_steps: int,
     *     steps: array{
     *         identity: array{done: bool, nif: bool, logo: bool},
     *         workplaces: array{done: bool}
     *     }
     * }
     */
    public function evaluate(Company $company): array
    {
        $nifOk = trim((string) ($company->nif ?? '')) !== '';
        $logoOk = filled($company->logo);
        $identityOk = $nifOk && $logoOk;

        $workplaceOk = Workplace::query()
            ->where('company_id', $company->id)
            ->where('status', WorkplaceStatus::Active)
            ->exists();

        $completed = (int) $identityOk + (int) $workplaceOk;

        return [
            'complete' => $identityOk && $workplaceOk,
            'completed_steps' => $completed,
            'total_steps' => 2,
            'steps' => [
                'identity' => [
                    'done' => $identityOk,
                    'nif' => $nifOk,
                    'logo' => $logoOk,
                ],
                'workplaces' => [
                    'done' => $workplaceOk,
                ],
            ],
        ];
    }
}
