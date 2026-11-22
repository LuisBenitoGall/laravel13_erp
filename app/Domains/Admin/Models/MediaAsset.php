<?php

namespace App\Domains\Admin\Models;

use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Activo de Galería (imagen reutilizable). company_id null = plataforma.
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property string $alt
 * @property string $path
 * @property string|null $mime
 * @property int|null $width
 * @property int|null $height
 * @property int|null $bytes
 */
class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'alt',
        'path',
        'mime',
        'width',
        'height',
        'bytes',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'bytes' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** URL de entrega autenticada (nunca /storage/…). */
    public function url(): string
    {
        return route('admin.gallery.file', $this, absolute: false);
    }

    protected static function newFactory(): MediaAssetFactory
    {
        return MediaAssetFactory::new();
    }
}
