<?php

namespace App\Domains\HR\Http\Resources;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Employee */
class EmployeeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'contract_type_id' => $this->contract_type_id,
            'contract_type_name' => $this->contractType?->name,
            'workplace_id' => $this->workplace_id,
            'workplace_name' => $this->workplace?->name,
            'job_title' => $this->job_title,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'weekly_hours' => $this->weekly_hours,
            'time_control' => (bool) $this->time_control,
            'year_salary' => $this->year_salary,
            'status' => $this->status->value,
            'observations' => $this->observations,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'can' => [
                'update' => $user?->can('update', $this->resource) ?? false,
                'renew' => $this->status === EmployeeStatus::Active
                    && ($user?->can('renew', $this->resource) ?? false),
                'end' => $this->status === EmployeeStatus::Active
                    && ($user?->can('end', $this->resource) ?? false),
                'delete' => $user?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
