<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\TownFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global geográfico (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property int $province_id
 * @property GeographyStatus $status
 * @property-read Province $province
 */
class Town extends Model
{
    /** @use HasFactory<TownFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'province_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => GeographyStatus::class,
        ];
    }

    /** @return BelongsTo<Province, $this> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    protected static function newFactory(): TownFactory
    {
        return TownFactory::new();
    }
}
