<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCompanyStatus
{
    public function handle(Company $company, CompanyStatus $status): Company
    {
        return DB::transaction(function () use ($company, $status): Company {
            $current = $company->status;

            if ($current === $status) {
                return $company;
            }

            if (! in_array($status, $current->transitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => __('transicion_estado_no_permitida'),
                ]);
            }

            $company->update(['status' => $status]);

            return $company->refresh();
        });
    }
}
