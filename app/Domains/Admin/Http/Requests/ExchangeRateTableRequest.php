<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class ExchangeRateTableRequest extends TableRequest
{
    protected array $sortable = ['pair_label', 'rate_ab', 'rate_ba', 'rate_date', 'created_at'];

    protected array $filterable = ['pair_label', 'rate_ab', 'rate_ba', 'rate_date', 'search'];
}
