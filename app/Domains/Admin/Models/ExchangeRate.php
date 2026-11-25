<?php

namespace App\Domains\Admin\Models;

use App\Support\Models\Concerns\Auditable;
use Database\Factories\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Catálogo global de cotizaciones (excepción R-TEN-01 --global; sin company_id).
 * Par dirigido from_currency → to_currency. Vigente = mayor rate_date ≤ D.
 *
 * @property int $id
 * @property int $from_currency_id
 * @property int $to_currency_id
 * @property string $rate
 * @property Carbon $rate_date
 * @property-read Currency $fromCurrency
 * @property-read Currency $toCurrency
 */
class ExchangeRate extends Model
{
    /** @use HasFactory<ExchangeRateFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'rate_date',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'rate_date' => 'date',
        ];
    }

    protected static function newFactory(): ExchangeRateFactory
    {
        return ExchangeRateFactory::new();
    }

    /** @return BelongsTo<Currency, $this> */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /** @return BelongsTo<Currency, $this> */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }
}
