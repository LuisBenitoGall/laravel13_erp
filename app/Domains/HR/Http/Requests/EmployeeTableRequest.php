<?php

namespace App\Domains\HR\Http\Requests;

use App\Support\Table\TableRequest;

class EmployeeTableRequest extends TableRequest
{
    protected array $sortable = ['job_title', 'start_date', 'end_date', 'weekly_hours', 'status', 'created_at'];

    protected array $filterable = [
        'user_name',
        'job_title',
        'start_date',
        'end_date',
        'status',
        'contract_type_id',
    ];
}
