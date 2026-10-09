<?php

namespace App\Domains\Partners\Models;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Models\User;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant organizativo (Partners). Excepción R-TEN-01: es el tenant, no un hijo scoped.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $tradename
 * @property string|null $nif
 * @property CompanyStatus $status
 * @property string|null $logo
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tradename',
        'nif',
        'status',
        'logo',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
        ];
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === CompanyStatus::Active;
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
