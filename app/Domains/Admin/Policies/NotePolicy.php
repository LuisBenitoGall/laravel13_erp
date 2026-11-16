<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Note;
use App\Models\User;

/**
 * Notas personales: sin Spatie Functionality (D2/D6). Ownership por user_id.
 */
class NotePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Note $note): bool
    {
        return (int) $note->user_id === (int) $user->id;
    }
}
