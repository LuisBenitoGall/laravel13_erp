<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\ProvinceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global geográfico (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property int $country_id
 * @property GeographyStatus $status
 * @property-read Country $country
 * @property-read Collection<int, Town> $towns
 */
class Province extends Model
{
    /** @use HasFactory<ProvinceFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => GeographyStatus::class,
        ];
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** @return HasMany<Town, $this> */
    public function towns(): HasMany
    {
        return $this->hasMany(Town::class);
    }

    protected static function newFactory(): ProvinceFactory
    {
        return ProvinceFactory::new();
    }
}
