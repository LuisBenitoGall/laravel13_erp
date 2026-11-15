<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\WorkplaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Centro de trabajo de la company en sesión (≠ Worksite / Workspace).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $slug
 * @property string|null $address
 * @property string|null $cp
 * @property int|null $town_id
 * @property bool $featured
 * @property WorkplaceStatus $status
 */
class Workplace extends Model
{
    /** @use HasFactory<WorkplaceFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'address',
        'cp',
        'town_id',
        'featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'status' => WorkplaceStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Town, $this> */
    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    protected static function newFactory(): WorkplaceFactory
    {
        return WorkplaceFactory::new();
    }
}
