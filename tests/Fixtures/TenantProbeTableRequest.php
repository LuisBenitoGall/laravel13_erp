<?php

namespace Tests\Fixtures;

use App\Support\Table\TableRequest;

final class TenantProbeTableRequest extends TableRequest
{
    protected array $sortable = ['name', 'id'];

    protected array $filterable = ['name', 'search'];
}
