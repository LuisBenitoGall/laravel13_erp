<?php

namespace App\Models;

use Database\Factories\UserDocFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'path', 'original_name', 'mime', 'size_bytes'])]
class UserDoc extends Model
{
    /** @use HasFactory<UserDocFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
