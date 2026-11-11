<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadUserImages
{
    /**
     * @param  list<UploadedFile>  $files
     * @return list<UserImage>
     */
    public function handle(User $owner, array $files, ?Company $company = null): array
    {
        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        $maxPerUser = (int) config('user_images.max_per_user', 20);
        $maxPerUpload = (int) config('user_images.max_per_upload', 20);

        if (count($files) > $maxPerUpload) {
            throw ValidationException::withMessages([
                'images' => __('imagenes_limite_subida', ['max' => $maxPerUpload]),
            ]);
        }

        $current = $owner->images()->count();
        if ($current + count($files) > $maxPerUser) {
            throw ValidationException::withMessages([
                'images' => __('imagenes_limite_usuario', ['max' => $maxPerUser]),
            ]);
        }

        return DB::transaction(function () use ($owner, $files): array {
            $created = [];

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                $path = sprintf('users/%d/images/%s.%s', $owner->id, Str::uuid()->toString(), $ext);
                Storage::disk('public')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path),
                );

                $created[] = UserImage::query()->create([
                    'user_id' => $owner->id,
                    'image' => $path,
                    'featured' => false,
                    'public' => false,
                ]);
            }

            return $created;
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
