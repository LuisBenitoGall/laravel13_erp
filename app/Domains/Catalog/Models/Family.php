<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Familia de catálogo de la company en sesión (maestro; Product 0..1 vía family_id).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $slug
 * @property FamilyStatus $status
 */
class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FamilyStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function newFactory(): FamilyFactory
    {
        return FamilyFactory::new();
    }
}
