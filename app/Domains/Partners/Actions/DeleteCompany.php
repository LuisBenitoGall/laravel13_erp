<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

class DeleteCompany
{
    public function handle(Company $company): void
    {
        DB::transaction(fn () => $company->delete());
    }
}
