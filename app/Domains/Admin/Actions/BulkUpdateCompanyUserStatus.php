<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BulkUpdateCompanyUserStatus
{
    public function __construct(private UpdateCompanyUserStatus $updateStatus) {}

    /**
     * @param  list<int>  $ids
     * @return int Number of status updates applied
     */
    public function handle(Company $company, array $ids, bool $status): int
    {
        return (int) DB::transaction(function () use ($company, $ids, $status): int {
            $count = 0;

            foreach ($ids as $id) {
                $member = User::query()->find($id);

                if ($member === null) {
                    continue;
                }

                if (! $member->companies()->where('companies.id', $company->id)->exists()) {
                    continue;
                }

                $this->updateStatus->handle($company, $member, $status);
                $count++;
            }

            return $count;
        });
    }
}
