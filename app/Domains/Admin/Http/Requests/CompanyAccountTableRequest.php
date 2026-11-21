<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CompanyAccountTableRequest extends TableRequest
{
    protected array $sortable = ['start_date', 'end_date', 'price', 'status', 'created_at'];

    protected array $filterable = ['status', 'search'];
}
