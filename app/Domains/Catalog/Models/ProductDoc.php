<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductDocFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Documento adjunto de un Product (disco privado; public; sin featured).
 *
 * @property int $id
 * @property int $product_id
 * @property int $company_id
 * @property string $path
 * @property string $original_name
 * @property string $mime
 * @property int $size_bytes
 * @property bool $public
 */
class ProductDoc extends Model
{
    /** @use HasFactory<ProductDocFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'company_id',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'public',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
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

    /** URL de descarga autenticada (nunca /storage/…). */
    public function downloadUrl(): string
    {
        return route('products.docs.download', [
            'product' => $this->product_id,
            'productDoc' => $this,
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

    protected static function newFactory(): ProductDocFactory
    {
        return ProductDocFactory::new();
    }
}
