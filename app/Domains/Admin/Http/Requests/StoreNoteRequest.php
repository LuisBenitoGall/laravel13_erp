<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Note;
use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Note::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }
}
