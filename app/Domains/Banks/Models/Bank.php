<?php

namespace App\Domains\Banks\Models;

use App\Domains\Banks\Enums\BankStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\BankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Catálogo global de bancos (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $tradename
 * @property string|null $lei
 * @property string|null $eu_code
 * @property string|null $supervisor_code
 * @property string|null $swift
 * @property string|null $swift_source
 * @property Carbon|null $swift_updated_at
 * @property BankStatus $status
 */
class Bank extends Model
{
    /** @use HasFactory<BankFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tradename',
        'lei',
        'eu_code',
        'supervisor_code',
        'swift',
        'swift_source',
        'swift_updated_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => BankStatus::class,
            'swift_updated_at' => 'datetime',
        ];
    }

    protected static function newFactory(): BankFactory
    {
        return BankFactory::new();
    }
}
