<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class WorkplaceTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'cp', 'featured', 'status', 'created_at'];

    protected array $filterable = ['name', 'cp', 'featured', 'status', 'search'];
}
