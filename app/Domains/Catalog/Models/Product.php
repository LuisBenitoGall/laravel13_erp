<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Producto del catálogo de la company en sesión (físico o servicio).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property ProductType $type
 * @property ProductStatus $status
 * @property int|null $family_id
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'family_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'status' => ProductStatus::class,
            'family_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Family, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /** @return HasMany<ProductDoc, $this> */
    public function docs(): HasMany
    {
        return $this->hasMany(ProductDoc::class);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
