<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class VatTypeTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'rate', 'equivalence_surcharge', 'country_id', 'status', 'created_at'];

    protected array $filterable = ['name', 'rate', 'equivalence_surcharge', 'country_id', 'status', 'search'];
}
