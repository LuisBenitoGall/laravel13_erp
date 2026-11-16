<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateNote;
use App\Domains\Admin\Actions\DeleteNote;
use App\Domains\Admin\Http\Requests\StoreNoteRequest;
use App\Domains\Admin\Models\Note;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request, CreateNote $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return back()->with('success', __('nota_guardada'));
    }

    public function destroy(Note $note, DeleteNote $action): RedirectResponse
    {
        $this->authorize('delete', $note);
        $action->handle($note);

        return back()->with('success', __('nota_eliminada'));
    }
}
