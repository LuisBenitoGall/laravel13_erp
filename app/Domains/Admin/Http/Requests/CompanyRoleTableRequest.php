<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CompanyRoleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'description', 'created_at'];

    protected array $filterable = ['name', 'description', 'search'];
}
