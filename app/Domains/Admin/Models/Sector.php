<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\SectorStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\SectorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Catálogo global de sectores (excepción R-TEN-01 --global; sin company_id).
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property SectorStatus $status
 */
class Sector extends Model
{
    /** @use HasFactory<SectorFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name'];

    protected $fillable = [
        'slug',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SectorStatus::class,
        ];
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    protected static function newFactory(): SectorFactory
    {
        return SectorFactory::new();
    }
}
