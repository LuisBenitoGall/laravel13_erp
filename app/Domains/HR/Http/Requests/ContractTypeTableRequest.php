<?php

namespace App\Domains\HR\Http\Requests;

use App\Support\Table\TableRequest;

class ContractTypeTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'duration', 'with_holidays', 'status', 'created_at'];

    protected array $filterable = ['name', 'with_holidays', 'status', 'search'];
}
