<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Support\Table\TableRequest;

class TagTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'slug', 'status', 'updated_at', 'created_at'];

    protected array $filterable = ['name', 'slug', 'status', 'search'];
}
