<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Categoría de catálogo (árbol N; sin vínculo Product/Family aún).
 *
 * @property int $id
 * @property int $company_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property CategoryStatus $status
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => CategoryStatus::class,
            'parent_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * IDs de descendientes no borrados (BFS).
     *
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        $ids = collect();
        $frontier = $this->children()->pluck('id');

        while ($frontier->isNotEmpty()) {
            $ids = $ids->merge($frontier);
            $frontier = self::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id');
        }

        return $ids->unique()->values();
    }

    public function isDescendantOf(Category $other): bool
    {
        if ($this->is($other)) {
            return false;
        }

        $current = $this->parent;

        while ($current !== null) {
            if ($current->is($other)) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    /** ¿Asignar $candidate como padre de $this crearía un ciclo? */
    public function wouldCycleWithParent(?Category $candidate): bool
    {
        if ($candidate === null) {
            return false;
        }

        if ($candidate->is($this)) {
            return true;
        }

        return $candidate->isDescendantOf($this);
    }

    public function hasActiveChildren(): bool
    {
        return $this->children()->exists();
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
