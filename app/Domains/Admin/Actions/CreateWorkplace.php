<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Support\Strings\UniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CreateWorkplace
{
    /**
     * @param  array<string, mixed>  $data
     * @param  Company|null  $company  Tenant explícito (Delegación); null = CurrentCompany.
     */
    public function handle(array $data, ?Company $company = null): Workplace
    {
        return DB::transaction(function () use ($data, $company): Workplace {
            $scope = fn (): Builder => $company === null
                ? Workplace::query()
                : Workplace::query()->withoutGlobalScope('company')->where('company_id', $company->id);

            $isFirst = $scope()->doesntExist();

            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => $scope()->withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= WorkplaceStatus::Active->value;
            $data['featured'] = $isFirst
                ? true
                : (bool) ($data['featured'] ?? false);

            if ($data['featured']) {
                $scope()->where('featured', true)->update(['featured' => false]);
            }

            if ($company !== null) {
                $data['company_id'] = $company->id;
            }

            return Workplace::query()->create($data);
        });
    }
}
