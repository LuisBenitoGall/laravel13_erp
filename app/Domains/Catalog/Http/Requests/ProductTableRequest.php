<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Support\Table\TableRequest;

class ProductTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'type', 'status', 'updated_at', 'created_at'];

    protected array $filterable = ['name', 'type', 'status', 'search'];
}
