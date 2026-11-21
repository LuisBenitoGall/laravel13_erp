<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\AccountStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global de cuentas SaaS / plantillas (excepción R-TEN-01 --global; sin company_id).
 * ≠ Module `cuenta`, ≠ AccountingAccount.
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 * @property string $rate
 * @property int|null $duration
 * @property AccountStatus $status
 * @property bool $is_default
 */
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rate',
        'duration',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'duration' => 'integer',
            'status' => AccountStatus::class,
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }
}
