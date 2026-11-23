<?php

namespace App\Domains\HR\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\HR\Enums\ContractTypeStatus;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\ContractTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tipo de contrato laboral de la company en sesión (≠ periodo Employee).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $slug
 * @property int|null $duration Duración en días
 * @property bool $with_holidays
 * @property ContractTypeStatus $status
 */
class ContractType extends Model
{
    /** @use HasFactory<ContractTypeFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'duration',
        'with_holidays',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'with_holidays' => 'boolean',
            'status' => ContractTypeStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Tipo indefinido: sin duración en días (convención A — no flag aparte).
     */
    public function isIndefinite(): bool
    {
        return $this->duration === null;
    }

    /**
     * Tipos ofrecibles en altas nuevas (no borrados, Active).
     *
     * @param  Builder<ContractType>  $query
     * @return Builder<ContractType>
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('status', ContractTypeStatus::Active);
    }

    protected static function newFactory(): ContractTypeFactory
    {
        return ContractTypeFactory::new();
    }
}
