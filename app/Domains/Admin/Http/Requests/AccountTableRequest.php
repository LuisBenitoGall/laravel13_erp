<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class AccountTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'rate', 'duration', 'status', 'is_default', 'created_at'];

    protected array $filterable = ['name', 'status', 'is_default', 'search'];
}
