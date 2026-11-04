<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global geográfico (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $code
 * @property string|null $alfa2
 * @property string|null $alfa3
 * @property string|null $flag
 * @property GeographyStatus $status
 * @property-read Collection<int, Province> $provinces
 * @property-read Collection<int, VatType> $vatTypes
 */
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'alfa2',
        'alfa3',
        'flag',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => GeographyStatus::class,
        ];
    }

    /** @return HasMany<Province, $this> */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    /** @return HasMany<VatType, $this> */
    public function vatTypes(): HasMany
    {
        return $this->hasMany(VatType::class);
    }

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }
}
