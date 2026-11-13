<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateUserImagePublic
{
    public function handle(UserImage $image, bool $public, ?Company $company = null): UserImage
    {
        $owner = $image->user;
        if ($owner === null) {
            throw ValidationException::withMessages([
                'image' => __('imagen_owner_invalido'),
            ]);
        }

        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        return DB::transaction(function () use ($image, $public): UserImage {
            $image->update(['public' => $public]);

            return $image->refresh();
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
