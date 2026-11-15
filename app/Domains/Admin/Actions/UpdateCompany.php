<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Support\Html\AllowedHtmlSanitizer;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateCompany
{
    public function __construct(
        private readonly AuthorizeBasicModulesForCompany $authorizeBasicModulesForCompany,
        private readonly AllowedHtmlSanitizer $htmlSanitizer,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Company::withTrashed()
                    ->where('slug', $slug)
                    ->whereKeyNot($company->id)
                    ->exists(),
            );

            if (array_key_exists('excerpt', $data) && is_array($data['excerpt'])) {
                $data['excerpt'] = array_map(
                    function (mixed $value): ?string {
                        if (! is_string($value)) {
                            return null;
                        }

                        $trimmed = trim($value);

                        return $trimmed === '' ? null : $trimmed;
                    },
                    $data['excerpt'],
                );
            }

            if (array_key_exists('description', $data) && is_array($data['description'])) {
                $data['description'] = array_map(
                    fn (mixed $html): ?string => $this->htmlSanitizer->sanitize(
                        is_string($html) ? $html : null,
                    ),
                    $data['description'],
                );
            }

            $wasActive = $company->status === CompanyStatus::Active;
            $company->update($data);
            $company->refresh();

            if (! $wasActive && $company->status === CompanyStatus::Active) {
                $this->authorizeBasicModulesForCompany->handle($company);
            }

            return $company;
        });
    }
}
