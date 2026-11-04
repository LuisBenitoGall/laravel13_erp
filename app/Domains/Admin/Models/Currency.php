<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global de monedas (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $code
 * @property string|null $number
 * @property string|null $symbol
 * @property CurrencyStatus $status
 */
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'number',
        'symbol',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CurrencyStatus::class,
        ];
    }

    protected static function newFactory(): CurrencyFactory
    {
        return CurrencyFactory::new();
    }
}
