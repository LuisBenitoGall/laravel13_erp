<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkUserPhonePrimary
{
    /**
     * @param  Company|null  $company  Camino company-users: assert perímetro.
     */
    public function handle(Phone $phone, ?Company $company = null): Phone
    {
        $owner = $phone->phoneable;
        if (! $owner instanceof User) {
            throw ValidationException::withMessages([
                'phone' => __('telefono_owner_invalido'),
            ]);
        }

        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        return DB::transaction(function () use ($phone, $owner): Phone {
            $owner->phones()
                ->whereKeyNot($phone->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $phone->update(['is_primary' => true]);

            return $phone->refresh();
        });
    }

    private function assertBelongsToCompany(Company $company, User $member): void
    {
        if (! $member->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }
    }
}
