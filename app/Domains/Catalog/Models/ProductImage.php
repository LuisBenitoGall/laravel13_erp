<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Imagen adjunta de un Product (disco privado; featured/public).
 *
 * @property int $id
 * @property int $product_id
 * @property int $company_id
 * @property string $image
 * @property bool $featured
 * @property bool $public
 */
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'company_id',
        'image',
        'featured',
        'public',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'public' => 'boolean',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** URL de entrega autenticada (nunca /storage/…). */
    public function url(): string
    {
        return route('products.images.file', [
            'product' => $this->product_id,
            'productImage' => $this,
        ], absolute: false);
    }

    /**
     * Elegible fuera de la ficha Edit: public ∧ Product Active (D3).
     */
    public function isEligibleOutsideEdit(?Product $product = null): bool
    {
        $owner = $product ?? $this->product;

        if ($owner === null) {
            return false;
        }

        return $this->public && $owner->status === ProductStatus::Active;
    }

    protected static function newFactory(): ProductImageFactory
    {
        return ProductImageFactory::new();
    }
}
