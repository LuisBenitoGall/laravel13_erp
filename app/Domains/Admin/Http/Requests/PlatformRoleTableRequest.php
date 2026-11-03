<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class PlatformRoleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'description', 'universal', 'created_at'];

    protected array $filterable = ['name', 'description', 'universal', 'search'];
}
