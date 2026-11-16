<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateNote
{
    /** @param  array{title: string, note: string}  $data */
    public function handle(User $user, array $data): Note
    {
        return DB::transaction(function () use ($user, $data): Note {
            $count = Note::query()
                ->where('user_id', $user->id)
                ->count();

            if ($count >= Note::MAX_PER_USER) {
                throw ValidationException::withMessages([
                    'title' => __('notas_limite_alcanzado'),
                ]);
            }

            return Note::query()->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'note' => $data['note'],
            ]);
        });
    }
}
