<?php

namespace App\Http\Controllers;

use App\Domains\Admin\Http\Resources\NoteResource;
use App\Domains\Admin\Http\Resources\PreferenceResource;
use App\Domains\Admin\Models\Note;
use App\Domains\Admin\Models\Preference;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $userId = $request->user()->id;

        $preferences = Preference::query()
            ->where('user_id', $userId)
            ->with('module:id,color,icon')
            ->orderBy('name')
            ->get();

        $notes = Note::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Dashboard', [
            'preferences' => PreferenceResource::collection($preferences)->resolve(),
            'notes' => NoteResource::collection($notes)->resolve(),
        ]);
    }
}
