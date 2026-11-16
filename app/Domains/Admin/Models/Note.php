<?php

namespace App\Domains\Admin\Models;

use App\Models\User;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nota interna personal del usuario (sin tenant / company_id).
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $note
 */
class Note extends Model
{
    public const MAX_PER_USER = 50;

    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'note',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }
}
