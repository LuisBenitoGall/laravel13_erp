<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class ModuleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'slug', 'level', 'status', 'created_at'];

    protected array $filterable = ['status', 'level', 'search'];
}
