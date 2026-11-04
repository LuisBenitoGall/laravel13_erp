<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CurrencyTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'code', 'number', 'symbol', 'status', 'created_at'];

    protected array $filterable = ['name', 'code', 'number', 'symbol', 'status', 'search'];
}
