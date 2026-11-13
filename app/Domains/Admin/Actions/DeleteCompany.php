<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;

class DeleteCompany
{
    public function handle(Company $company): void
    {
        DB::transaction(fn () => $company->delete());
    }
}
