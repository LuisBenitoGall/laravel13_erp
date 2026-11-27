<?php

namespace App\Domains\Calendar\Http\Requests;

use App\Domains\Calendar\Models\WorkCalendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertWorkCalendarDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WorkCalendar $calendar */
        $calendar = $this->route('work_calendar');

        return $this->user()->can('update', $calendar);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var WorkCalendar $calendar */
        $calendar = $this->route('work_calendar');

        return [
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'day_type_id' => [
                'required',
                'integer',
                Rule::exists('day_types', 'id')
                    ->where(fn ($query) => $query
                        ->where('company_id', $calendar->company_id)
                        ->whereNull('deleted_at')),
            ],
        ];
    }
}
