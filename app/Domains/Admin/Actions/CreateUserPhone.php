<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\PhoneType;
use App\Domains\Admin\Models\Company;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUserPhone
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
    public function handle(User $owner, array $data, ?Company $company = null): Phone
    {
        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        return DB::transaction(function () use ($owner, $data): Phone {
            $e164 = $data['e164'];
            $this->purgeSoftDeletedDuplicate($owner, $e164);

            $isPrimary = (bool) ($data['is_primary'] ?? false);
            if ($isPrimary) {
                $this->clearPrimaryForOwner($owner);
            }

            return $owner->phones()->create([
                'e164' => $e164,
                'country_id' => null,
                'ext' => $data['ext'] ?? null,
                'type' => PhoneType::from($data['type']),
                'label' => $data['label'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_whatsapp' => (bool) ($data['is_whatsapp'] ?? false),
                'is_primary' => $isPrimary,
                'is_verified' => false,
                'verified_at' => null,
            ]);
        });
    }

    private function purgeSoftDeletedDuplicate(User $owner, string $e164): void
    {
        Phone::onlyTrashed()
            ->where('phoneable_type', $owner->getMorphClass())
            ->where('phoneable_id', $owner->id)
            ->where('e164', $e164)
            ->forceDelete();
    }

    private function clearPrimaryForOwner(User $owner): void
    {
        $owner->phones()
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
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
