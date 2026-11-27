<?php

namespace App\Domains\Calendar\Http\Requests;

use App\Support\Table\TableRequest;

class DayTypeTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'color', 'is_working', 'created_at'];

    protected array $filterable = ['name', 'is_working', 'search'];
}
