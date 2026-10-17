<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class PlatformRoleTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'universal', 'created_at'];

    protected array $filterable = ['universal', 'search'];
}
