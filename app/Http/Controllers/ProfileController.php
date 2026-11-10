<?php

namespace App\Http\Controllers;

use App\Domains\Admin\Actions\UpdateOwnPassword;
use App\Domains\Admin\Actions\UpdateUserProfile;
use App\Domains\Admin\Http\Requests\UpdateOwnPasswordRequest;
use App\Domains\Admin\Http\Requests\UpdateUserProfileRequest;
use App\Domains\Admin\Support\UserEditPageData;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request, UserEditPageData $pageData): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render(
            'Users/Edit',
            $pageData->build($user, $user, 'profile'),
        );
    }

    public function update(
        UpdateUserProfileRequest $request,
        UpdateUserProfile $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $action->handle($user, $user, $request->validated());

        return back()->with('success', __('perfil_actualizado'));
    }

    public function updatePassword(
        UpdateOwnPasswordRequest $request,
        UpdateOwnPassword $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $action->handle($user, $user, $request->validated());

        return back()->with('success', __('contrasena_actualizada'));
    }
}
