<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class AccountingAccountTypeTableRequest extends TableRequest
{
    protected array $sortable = ['code', 'name', 'created_at'];

    protected array $filterable = ['code', 'name', 'search'];
}
