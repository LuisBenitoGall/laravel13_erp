<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Proyección RO de periodos Employee en ficha User (R-TEN-10).
 *
 * @mixin Employee
 */
class UserEmploymentPeriodResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->company?->name,
            'workplace_id' => $this->workplace_id,
            'workplace_name' => $this->workplace?->name,
            'contract_type_id' => $this->contract_type_id,
            'contract_type_name' => $this->contractType?->name,
            'job_title' => $this->job_title,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'weekly_hours' => $this->weekly_hours,
            'year_salary' => $this->year_salary,
            'observations' => $this->observations,
        ];
    }
}
