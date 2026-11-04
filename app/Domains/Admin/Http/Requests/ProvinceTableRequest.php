<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class ProvinceTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'status', 'created_at'];

    protected array $filterable = ['name', 'status', 'search'];
}
