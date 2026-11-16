<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreatePreference;
use App\Domains\Admin\Actions\DeletePreference;
use App\Domains\Admin\Http\Requests\StorePreferenceRequest;
use App\Domains\Admin\Models\Preference;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PreferenceController extends Controller
{
    public function store(StorePreferenceRequest $request, CreatePreference $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return back()->with('success', __('favorito_guardado'));
    }

    public function destroy(Preference $preference, DeletePreference $action): RedirectResponse
    {
        $this->authorize('delete', $preference);
        $action->handle($preference);

        return back()->with('success', __('favorito_eliminado'));
    }
}
