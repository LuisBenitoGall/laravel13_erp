<?php

namespace App\Models;

use Database\Factories\UserImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['image', 'user_id', 'featured', 'public'])]
class UserImage extends Model
{
    /** @use HasFactory<UserImageFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'public' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        // Relative path: avoids broken absolute URLs when APP_URL ≠ host de Laragon.
        return '/storage/'.ltrim($this->image, '/');
    }

    public function isPubliclyVisible(?User $owner = null): bool
    {
        $owner ??= $this->user;

        if ($owner === null) {
            return false;
        }

        return $this->public && (bool) $owner->is_public;
    }
}
