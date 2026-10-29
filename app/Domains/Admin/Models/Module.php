<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Catálogo global SaaS (R-AUT-07; excepción R-TEN-01 --global).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $label
 * @property string $color
 * @property string $icon
 * @property ModuleLevel $level
 * @property ModuleStatus $status
 * @property-read Collection<int, Functionality> $functionalities
 */
class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /**
     * Slugs de shell usados en middleware `module:` — Inactive en catálogo
     * deja la app inaccesible (403). No se pueden desactivar.
     *
     * @var list<string>
     */
    public const SHELL_SLUGS = ['admin', 'workspace'];

    /** @var list<string> */
    public array $translatable = ['label', 'explanation'];

    protected $fillable = [
        'name',
        'slug',
        'label',
        'color',
        'icon',
        'level',
        'status',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'level' => ModuleLevel::class,
            'status' => ModuleStatus::class,
        ];
    }

    public function isShellCritical(): bool
    {
        return in_array($this->slug, self::SHELL_SLUGS, true);
    }

    /** @return HasMany<Functionality, $this> */
    public function functionalities(): HasMany
    {
        return $this->hasMany(Functionality::class);
    }

    protected static function newFactory(): ModuleFactory
    {
        return ModuleFactory::new();
    }
}
