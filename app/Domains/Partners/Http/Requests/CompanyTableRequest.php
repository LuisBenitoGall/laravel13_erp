<?php

namespace App\Domains\Partners\Http\Requests;

use App\Support\Table\TableRequest;

class CompanyTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'created_at'];

    protected array $filterable = ['search'];
}
