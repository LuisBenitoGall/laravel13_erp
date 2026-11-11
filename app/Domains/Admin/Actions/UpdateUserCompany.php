<?php

namespace App\Domains\Admin\Actions;

use App\Models\UserCompany;
use Illuminate\Support\Facades\DB;

class UpdateUserCompany
{
    /**
     * @param  array{position: ?string, department: ?string}  $data
     */
    public function handle(UserCompany $membership, array $data): UserCompany
    {
        return DB::transaction(function () use ($membership, $data): UserCompany {
            $membership->fill([
                'position' => $data['position'],
                'department' => $data['department'],
            ]);
            $membership->save();

            return $membership->refresh();
        });
    }
}
