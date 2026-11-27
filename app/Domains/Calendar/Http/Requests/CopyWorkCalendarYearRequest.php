<?php

namespace App\Domains\Calendar\Http\Requests;

use App\Domains\Calendar\Models\WorkCalendar;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyWorkCalendarYearRequest extends FormRequest
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
        $companyId = app(CurrentCompany::class)->id();

        return [
            'year' => ['required', 'integer', 'min:1970', 'max:2100'],
            'target_workplace_id' => [
                'required',
                'integer',
                Rule::exists('workplaces', 'id')
                    ->where(fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->whereNull('deleted_at')),
            ],
        ];
    }
}
