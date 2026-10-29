<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class ModuleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'color', 'icon', 'level', 'created_at', 'status'];

    protected array $filterable = ['name', 'color', 'icon', 'level', 'created_at', 'status', 'search'];
}
