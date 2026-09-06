<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\FunctionalityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Unidad de permiso/menú dentro de un Module (R-AUT-07).
 *
 * @property int $id
 * @property int $module_id
 * @property string $name
 * @property string $slug
 * @property string $label
 * @property FunctionalityStatus $status
 * @property-read Module $module
 */
class Functionality extends Model
{
    /** @use HasFactory<FunctionalityFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['label'];

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'label',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FunctionalityStatus::class,
        ];
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    protected static function newFactory(): FunctionalityFactory
    {
        return FunctionalityFactory::new();
    }
}
