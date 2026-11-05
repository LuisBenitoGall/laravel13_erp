<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class AllUserTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'surname', 'email', 'status', 'created_at'];

    protected array $filterable = ['name', 'surname', 'email', 'status', 'search'];
}
