<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Contenido Admin (Help|Article). Alta: company_id null; status Inactive.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property ContentKind $kind
 * @property int|null $company_id
 * @property array<string, string>|string $title
 * @property array<string, string>|string $excerpt
 * @property array<string, string>|string $content
 * @property string|null $referrer
 * @property string|null $video
 * @property ContentStatus $status
 */
class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'excerpt', 'content'];

    protected $fillable = [
        'name',
        'code',
        'kind',
        'company_id',
        'title',
        'excerpt',
        'content',
        'referrer',
        'video',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ContentKind::class,
            'status' => ContentStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected static function newFactory(): ContentFactory
    {
        return ContentFactory::new();
    }
}
