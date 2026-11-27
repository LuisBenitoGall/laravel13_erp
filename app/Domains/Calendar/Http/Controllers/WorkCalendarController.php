<?php

namespace App\Domains\Calendar\Http\Controllers;

use App\Domains\Admin\Models\Workplace;
use App\Domains\Calendar\Actions\CopyWorkCalendarYear;
use App\Domains\Calendar\Actions\DeleteWorkCalendarDay;
use App\Domains\Calendar\Actions\FindOrCreateWorkCalendar;
use App\Domains\Calendar\Actions\InheritWorkCalendarYear;
use App\Domains\Calendar\Actions\UpsertWorkCalendarDay;
use App\Domains\Calendar\Http\Requests\CopyWorkCalendarYearRequest;
use App\Domains\Calendar\Http\Requests\InheritWorkCalendarYearRequest;
use App\Domains\Calendar\Http\Requests\UpsertWorkCalendarDayRequest;
use App\Domains\Calendar\Http\Resources\DayTypeResource;
use App\Domains\Calendar\Http\Resources\WorkCalendarDayResource;
use App\Domains\Calendar\Models\DayType;
use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use App\Http\Controllers\Controller;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class WorkCalendarController extends Controller
{
    public function index(Request $request, FindOrCreateWorkCalendar $findOrCreate): InertiaResponse
    {
        $this->authorize('viewAny', WorkCalendar::class);

        $year = (int) $request->integer('year', (int) now()->year);
        if ($year < 1970 || $year > 2100) {
            $year = (int) now()->year;
        }

        $workplaces = Workplace::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Workplace $w): array => ['id' => $w->id, 'name' => $w->name])
            ->all();

        $dayTypes = DayType::query()
            ->orderBy('name')
            ->get()
            ->map(fn (DayType $type): array => DayTypeResource::make($type)->resolve())
            ->all();

        $workplaceId = $request->integer('workplace_id') ?: null;
        $calendarPayload = null;
        $days = [];

        if ($workplaceId !== null) {
            $workplace = Workplace::query()->findOrFail($workplaceId);

            $existing = WorkCalendar::withTrashed()
                ->where('workplace_id', $workplace->id)
                ->first();

            if ($existing === null || $existing->trashed()) {
                $this->authorize('create', WorkCalendar::class);
            }

            $calendar = $findOrCreate->handle($workplace);
            $this->authorize('view', $calendar);

            $calendarPayload = [
                'id' => $calendar->id,
                'workplace_id' => $calendar->workplace_id,
                'company_id' => $calendar->company_id,
            ];

            $days = WorkCalendarDay::query()
                ->with('dayType')
                ->where('work_calendar_id', $calendar->id)
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get()
                ->map(fn (WorkCalendarDay $day): array => WorkCalendarDayResource::make($day)->resolve())
                ->all();
        }

        return Inertia::render('HR/Calendars/Index', [
            'year' => $year,
            'workplaces' => $workplaces,
            'day_types' => $dayTypes,
            'selected_workplace_id' => $workplaceId,
            'calendar' => $calendarPayload,
            'days' => $days,
            'can' => [
                'update' => $request->user()?->can('calendars.update') ?? false,
                'manage_day_types' => $request->user()?->can('day-types.view') ?? false,
            ],
        ]);
    }

    public function upsertDay(
        UpsertWorkCalendarDayRequest $request,
        WorkCalendar $workCalendar,
        UpsertWorkCalendarDay $action,
    ): RedirectResponse {
        $action->handle($workCalendar, $request->validated());

        return back()->with('success', __('dia_calendario_guardado'));
    }

    public function destroyDay(
        WorkCalendar $workCalendar,
        WorkCalendarDay $day,
        DeleteWorkCalendarDay $action,
    ): RedirectResponse {
        $this->authorize('update', $workCalendar);

        abort_unless((int) $day->work_calendar_id === (int) $workCalendar->id, 404);

        $action->handle($day);

        return back()->with('success', __('dia_calendario_eliminado'));
    }

    public function inherit(
        InheritWorkCalendarYearRequest $request,
        WorkCalendar $workCalendar,
        InheritWorkCalendarYear $action,
    ): RedirectResponse {
        $data = $request->validated();
        $action->handle($workCalendar, (int) $data['from_year'], (int) $data['to_year']);

        return redirect()
            ->route('rrhh.calendars.index', [
                'workplace_id' => $workCalendar->workplace_id,
                'year' => $data['to_year'],
            ])
            ->with('success', __('calendario_heredado'));
    }

    public function copy(
        CopyWorkCalendarYearRequest $request,
        WorkCalendar $workCalendar,
        CopyWorkCalendarYear $action,
        FindOrCreateWorkCalendar $findOrCreate,
    ): RedirectResponse {
        $data = $request->validated();
        $target = Workplace::query()->findOrFail($data['target_workplace_id']);

        abort_if(
            (int) $target->id === (int) $workCalendar->workplace_id,
            422,
        );

        abort_unless(
            (int) $target->company_id === (int) app(CurrentCompany::class)->id(),
            404,
        );

        $action->handle($workCalendar, $target, (int) $data['year'], $findOrCreate);

        return redirect()
            ->route('rrhh.calendars.index', [
                'workplace_id' => $target->id,
                'year' => $data['year'],
            ])
            ->with('success', __('calendario_copiado'));
    }
}
