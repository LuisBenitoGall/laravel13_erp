<?php

namespace App\Domains\Admin\Models;

use App\Models\User;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\TableFilterPresetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Preset de filtros avanzados ServerTable (user + company + table_key).
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property string $table_key
 * @property string $name
 * @property list<array{field: string, type: string, value: mixed}> $filters
 */
class TableFilterPreset extends Model
{
    public const MAX_PER_TABLE = 5;

    /** @use HasFactory<TableFilterPresetFactory> */
    use Auditable, BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'table_key',
        'name',
        'filters',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): TableFilterPresetFactory
    {
        return TableFilterPresetFactory::new();
    }
}
