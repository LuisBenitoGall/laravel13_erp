<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 * Universo del Directorio: Active + NIF no vacío (tras trim) + ≥1 sector Active;
 * filtro OR por sectores.
 */
final class CompanyDirectoryQuery
{
    /**
     * @param  list<int>  $sectorIds
     * @return Builder<Company>
     */
    public function base(array $sectorIds = []): Builder
    {
        $query = Company::query()
            ->where('status', CompanyStatus::Active)
            ->whereNotNull('nif')
            ->whereRaw("TRIM(nif) != ''")
            ->whereHas(
                'sectors',
                fn (Builder $sectors) => $sectors->where('sectors.status', SectorStatus::Active),
            );

        if ($sectorIds !== []) {
            $query->whereHas(
                'sectors',
                fn (Builder $sectors) => $sectors
                    ->where('sectors.status', SectorStatus::Active)
                    ->whereIn('sectors.id', $sectorIds),
            );
        }

        return $query;
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'name_desc' => $query->orderByDesc('name')->orderByDesc('id'),
            'recent' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderBy('name')->orderBy('id'),
        };
    }
}
