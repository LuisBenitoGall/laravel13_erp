<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Note;
use Illuminate\Support\Facades\DB;

class DeleteNote
{
    public function handle(Note $note): void
    {
        DB::transaction(fn () => $note->delete());
    }
}
