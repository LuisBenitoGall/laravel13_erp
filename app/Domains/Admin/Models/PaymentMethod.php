<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Catálogo global de métodos de pago (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property array<string, string>|string $name
 * @property PaymentMethodStatus $status
 */
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentMethodStatus::class,
        ];
    }

    protected static function newFactory(): PaymentMethodFactory
    {
        return PaymentMethodFactory::new();
    }
}
