<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Support\AssertsUserBelongsToCompany;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Models\UserDoc;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadUserDocs
{
    use AssertsUserBelongsToCompany;

    /**
     * @param  list<UploadedFile>  $files
     * @return list<UserDoc>
     */
    public function handle(User $owner, array $files, ?Company $company = null): array
    {
        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        $maxPerUser = (int) config('user_docs.max_per_user', 30);
        $maxPerUpload = (int) config('user_docs.max_per_upload', 10);

        if (count($files) > $maxPerUpload) {
            throw ValidationException::withMessages([
                'docs' => __('docs_limite_subida', ['max' => $maxPerUpload]),
            ]);
        }

        $current = $owner->docs()->count();
        if ($current + count($files) > $maxPerUser) {
            throw ValidationException::withMessages([
                'docs' => __('docs_limite_usuario', ['max' => $maxPerUser]),
            ]);
        }

        return DB::transaction(function () use ($owner, $files): array {
            $created = [];

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
                $path = sprintf('users/%d/docs/%s.%s', $owner->id, Str::uuid()->toString(), $ext);
                Storage::disk('local')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path),
                );

                $created[] = UserDoc::query()->create([
                    'user_id' => $owner->id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName() ?: basename($path),
                    'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize() ?: 0,
                ]);
            }

            return $created;
        });
    }
}
