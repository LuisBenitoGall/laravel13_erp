<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\VatTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global de tipos de IVA por país (excepción R-TEN-01 --global).
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $rate
 * @property string|null $equivalence_surcharge
 * @property VatTypeStatus $status
 * @property-read Country $country
 */
class VatType extends Model
{
    /** @use HasFactory<VatTypeFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'country_id',
        'name',
        'rate',
        'equivalence_surcharge',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'equivalence_surcharge' => 'decimal:2',
            'status' => VatTypeStatus::class,
        ];
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    protected static function newFactory(): VatTypeFactory
    {
        return VatTypeFactory::new();
    }
}
