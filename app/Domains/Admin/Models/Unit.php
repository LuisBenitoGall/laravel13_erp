<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\UnitStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global de unidades (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $symbol
 * @property UnitStatus $status
 */
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'symbol',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => UnitStatus::class,
        ];
    }

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }
}
