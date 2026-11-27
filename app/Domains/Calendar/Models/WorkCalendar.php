<?php

namespace App\Domains\Calendar\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\WorkCalendarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Calendario laboral 1:1 por Workplace (company_id = matriz dueña).
 *
 * @property int $id
 * @property int $company_id
 * @property int $workplace_id
 */
class WorkCalendar extends Model
{
    /** @use HasFactory<WorkCalendarFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'workplace_id',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Workplace, $this> */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    /** @return HasMany<WorkCalendarDay, $this> */
    public function days(): HasMany
    {
        return $this->hasMany(WorkCalendarDay::class);
    }

    protected static function newFactory(): WorkCalendarFactory
    {
        return WorkCalendarFactory::new();
    }
}
