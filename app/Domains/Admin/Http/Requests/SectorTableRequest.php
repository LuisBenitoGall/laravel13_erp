<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class SectorTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'slug', 'status', 'created_at'];

    protected array $filterable = ['name', 'slug', 'status', 'search'];
}
