<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CountryTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'code', 'alfa2', 'alfa3', 'status', 'created_at'];

    protected array $filterable = ['name', 'code', 'alfa2', 'alfa3', 'status', 'search'];
}
