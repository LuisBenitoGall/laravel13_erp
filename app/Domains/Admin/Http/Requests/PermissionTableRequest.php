<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class PermissionTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'created_at'];

    protected array $filterable = ['name', 'search'];
}
