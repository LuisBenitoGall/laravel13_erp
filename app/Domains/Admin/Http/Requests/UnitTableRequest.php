<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class UnitTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'symbol', 'status', 'created_at'];

    protected array $filterable = ['name', 'symbol', 'status', 'search'];
}
