<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteUserImage
{
    public function handle(UserImage $image, ?Company $company = null): void
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

        DB::transaction(function () use ($image): void {
            $path = $image->image;

            // Liberar featured_user_id (unique MySQL) antes del soft-delete.
            if ($image->featured) {
                $image->update(['featured' => false]);
            }

            $image->delete();

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
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
