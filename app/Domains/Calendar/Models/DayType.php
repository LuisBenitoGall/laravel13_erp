<?php

namespace App\Domains\Calendar\Models;

use App\Domains\Admin\Models\Company;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\DayTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tipo de jornada laboral de la company en sesión (catálogo Calendar).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $color
 * @property bool $is_working
 */
class DayType extends Model
{
    /** @use HasFactory<DayTypeFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'is_working',
    ];

    protected function casts(): array
    {
        return [
            'is_working' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<WorkCalendarDay, $this> */
    public function workCalendarDays(): HasMany
    {
        return $this->hasMany(WorkCalendarDay::class);
    }

    protected static function newFactory(): DayTypeFactory
    {
        return DayTypeFactory::new();
    }
}
