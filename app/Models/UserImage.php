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

    /**
     * URL de entrega autenticada (nunca /storage/…).
     *
     * @param  'profile'|'company'|'directory'  $context
     */
    public function url(string $context = 'profile'): string
    {
        return match ($context) {
            'company' => route('admin.company-users.images.file', [
                'user' => $this->user_id,
                'userImage' => $this,
            ], absolute: false),
            'directory' => route('admin.all-users.images.file', [
                'user' => $this->user_id,
                'userImage' => $this,
            ], absolute: false),
            default => route('admin.profile.images.file', $this, absolute: false),
        };
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
