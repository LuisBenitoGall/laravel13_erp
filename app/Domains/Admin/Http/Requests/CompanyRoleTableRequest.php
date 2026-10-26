<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CompanyRoleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'created_at'];

    protected array $filterable = ['search'];
}
