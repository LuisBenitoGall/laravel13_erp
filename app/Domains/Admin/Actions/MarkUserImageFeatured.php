<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkUserImageFeatured
{
    public function handle(UserImage $image, ?Company $company = null): UserImage
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

        return DB::transaction(function () use ($image, $owner): UserImage {
            // Activas + soft-deleted residuales (unique featured_user_id en MySQL).
            UserImage::withTrashed()
                ->where('user_id', $owner->id)
                ->whereKeyNot($image->id)
                ->where('featured', true)
                ->update(['featured' => false]);

            $image->update(['featured' => true]);

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
