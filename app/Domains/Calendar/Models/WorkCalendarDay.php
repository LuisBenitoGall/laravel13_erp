<?php

namespace App\Domains\Calendar\Models;

use App\Support\Models\Concerns\Auditable;
use Database\Factories\WorkCalendarDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Día/evento de un WorkCalendar (fecha única por calendario).
 *
 * @property int $id
 * @property int $work_calendar_id
 * @property Carbon $date
 * @property string $name
 * @property int $day_type_id
 */
class WorkCalendarDay extends Model
{
    /** @use HasFactory<WorkCalendarDayFactory> */
    use Auditable, HasFactory;

    protected $fillable = [
        'work_calendar_id',
        'date',
        'name',
        'day_type_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /** @return BelongsTo<WorkCalendar, $this> */
    public function workCalendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class);
    }

    /** @return BelongsTo<DayType, $this> */
    public function dayType(): BelongsTo
    {
        return $this->belongsTo(DayType::class);
    }

    protected static function newFactory(): WorkCalendarDayFactory
    {
        return WorkCalendarDayFactory::new();
    }
}
