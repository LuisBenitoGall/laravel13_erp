<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Support\Collection;

final class CompanyHierarchyOptions
{
    /**
     * @return array{
     *     parents: list<array{id: int, name: string}>,
     *     children: list<array{id: int, name: string}>
     * }
     */
    public function forEdit(Company $company, User $user): array
    {
        $linked = Company::query()
            ->forUser($user)
            ->whereKeyNot($company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $parents = $linked
            ->reject(fn (Company $candidate): bool => $company->wouldCycleWithParent($candidate))
            ->values();

        $children = $linked
            ->reject(fn (Company $candidate): bool => $company->wouldCycleWithChild($candidate))
            ->reject(fn (Company $candidate): bool => (int) $candidate->parent_id === (int) $company->id)
            ->values();

        return [
            'parents' => $this->mapOptions($parents),
            'children' => $this->mapOptions($children),
        ];
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @return list<array{id: int, name: string}>
     */
    private function mapOptions(Collection $companies): array
    {
        return $companies
            ->map(fn (Company $c): array => [
                'id' => $c->id,
                'name' => $c->name,
            ])
            ->values()
            ->all();
    }
}
