<?php

namespace App\Domains\Calendar\Http\Requests;

use App\Domains\Calendar\Models\WorkCalendar;
use Illuminate\Foundation\Http\FormRequest;

class InheritWorkCalendarYearRequest extends FormRequest
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
        return [
            'from_year' => ['required', 'integer', 'min:1970', 'max:2100'],
            'to_year' => ['required', 'integer', 'min:1970', 'max:2100', 'different:from_year'],
        ];
    }
}
