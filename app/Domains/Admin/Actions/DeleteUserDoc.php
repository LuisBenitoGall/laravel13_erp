<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\AssertsUserBelongsToCompany;
use App\Models\UserDoc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteUserDoc
{
    use AssertsUserBelongsToCompany;

    public function handle(UserDoc $doc, ?Company $company = null): void
    {
        $owner = $doc->user;
        if ($owner === null) {
            throw ValidationException::withMessages([
                'doc' => __('doc_owner_invalido'),
            ]);
        }

        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        DB::transaction(function () use ($doc): void {
            $path = $doc->path;
            $doc->delete();

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        });
    }
}
