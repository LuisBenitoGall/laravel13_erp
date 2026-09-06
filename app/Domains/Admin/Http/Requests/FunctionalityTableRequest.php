<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class FunctionalityTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'slug', 'status', 'created_at'];

    protected array $filterable = ['status', 'module_id', 'search'];
}
