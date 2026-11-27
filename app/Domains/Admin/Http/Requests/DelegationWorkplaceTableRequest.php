<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class DelegationWorkplaceTableRequest extends TableRequest
{
    protected array $sortable = [
        'name',
        'cp',
        'created_at',
        'is_preferred_seat',
    ];

    protected array $filterable = [
        'name',
        'cp',
        'town_name',
        'province_name',
        'country_name',
        'created_at',
        'search',
        'is_preferred_seat',
    ];
}
