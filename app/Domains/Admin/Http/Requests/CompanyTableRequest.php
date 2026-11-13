<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class CompanyTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'tradename', 'nif', 'created_at', 'status'];

    protected array $filterable = ['name', 'tradename', 'nif', 'created_at', 'status', 'search'];
}
