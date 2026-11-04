<?php

namespace App\Domains\Banks\Http\Requests;

use App\Support\Table\TableRequest;

class BankTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'tradename', 'eu_code', 'swift', 'status', 'created_at'];

    protected array $filterable = ['name', 'tradename', 'eu_code', 'swift', 'status', 'search'];
}
