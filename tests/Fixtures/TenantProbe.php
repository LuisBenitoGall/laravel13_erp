<?php

namespace Tests\Fixtures;

use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo sintético para tests de tenancy/auditoría. No es entidad de negocio.
 */
class TenantProbe extends Model
{
    use Auditable, BelongsToCompany;

    protected $table = 'tenant_probes';

    protected $fillable = [
        'name',
    ];
}
