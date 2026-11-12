<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Partners\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadCompanyLogo
{
    public function handle(Company $company, UploadedFile $file): Company
    {
        return DB::transaction(function () use ($company, $file): Company {
            $disk = Storage::disk('public');
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $path = sprintf('companies/%d/logo-%s.%s', $company->id, Str::uuid()->toString(), $ext);

            $disk->putFileAs(dirname($path), $file, basename($path));

            $previous = $company->logo;
            if (is_string($previous) && $previous !== '' && $disk->exists($previous)) {
                $disk->delete($previous);
            }

            $company->update(['logo' => $path]);

            return $company->refresh();
        });
    }
}
