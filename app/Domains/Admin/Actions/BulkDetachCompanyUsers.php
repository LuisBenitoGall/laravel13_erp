<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BulkDetachCompanyUsers
{
    public function __construct(private DetachCompanyUser $detach) {}

    /**
     * @param  list<int>  $ids
     * @return int Number of detachments applied
     */
    public function handle(Company $company, array $ids): int
    {
        return (int) DB::transaction(function () use ($company, $ids): int {
            $count = 0;

            foreach ($ids as $id) {
                $member = User::query()->find($id);

                if ($member === null) {
                    continue;
                }

                if (! $member->companies()->where('companies.id', $company->id)->exists()) {
                    continue;
                }

                $this->detach->handle($company, $member);
                $count++;
            }

            return $count;
        });
    }
}
