<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\PhoneType;
use App\Domains\Admin\Models\Company;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateUserPhone
{
    /**
     * @param  array{
     *     e164: string,
     *     ext?: string|null,
     *     type: string,
     *     label?: string|null,
     *     notes?: string|null,
     *     is_whatsapp?: bool,
     *     is_primary?: bool
     * }  $data
     * @param  Company|null  $company  Camino company-users: assert perímetro.
     */
    public function handle(Phone $phone, array $data, ?Company $company = null): Phone
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

        return DB::transaction(function () use ($phone, $owner, $data): Phone {
            $e164 = $data['e164'];
            $this->purgeSoftDeletedDuplicate($owner, $e164, $phone->id);

            $isPrimary = (bool) ($data['is_primary'] ?? false);
            if ($isPrimary) {
                $owner->phones()
                    ->whereKeyNot($phone->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $phone->update([
                'e164' => $e164,
                'country_id' => null,
                'ext' => $data['ext'] ?? null,
                'type' => PhoneType::from($data['type']),
                'label' => $data['label'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_whatsapp' => (bool) ($data['is_whatsapp'] ?? false),
                'is_primary' => $isPrimary,
            ]);

            return $phone->refresh();
        });
    }

    private function purgeSoftDeletedDuplicate(User $owner, string $e164, int $ignoreId): void
    {
        Phone::onlyTrashed()
            ->where('phoneable_type', $owner->getMorphClass())
            ->where('phoneable_id', $owner->id)
            ->where('e164', $e164)
            ->whereKeyNot($ignoreId)
            ->forceDelete();
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
