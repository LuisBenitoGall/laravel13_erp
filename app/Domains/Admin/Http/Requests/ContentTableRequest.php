<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class ContentTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'code', 'kind', 'status', 'created_at'];

    protected array $filterable = ['name', 'code', 'kind', 'status', 'search'];
}
