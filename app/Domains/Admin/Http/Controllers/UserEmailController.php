<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateUserEmail;
use App\Domains\Admin\Actions\DeleteUserEmail;
use App\Domains\Admin\Http\Requests\StoreUserEmailRequest;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserEmail;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserEmailController extends Controller
{
    public function storeForProfile(
        StoreUserEmailRequest $request,
        CreateUserEmail $action,
    ): RedirectResponse {
        /** @var User $owner */
        $owner = $request->user();
        $this->authorize('create', [UserEmail::class, $owner]);

        $action->handle($owner, $request->validated());

        return back()->with('success', __('email_adicional_creado'));
    }

    public function destroyForProfile(
        Request $request,
        UserEmail $userEmail,
        DeleteUserEmail $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userEmail->user_id === $actor->id, 403);
        $this->authorize('delete', $userEmail);

        $action->handle($userEmail);

        return back()->with('success', __('email_adicional_eliminado'));
    }

    public function storeForCompanyUser(
        StoreUserEmailRequest $request,
        User $user,
        CreateUserEmail $action,
    ): RedirectResponse {
        $this->authorize('create', [UserEmail::class, $user]);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($user, $request->validated(), $company);

        return back()->with('success', __('email_adicional_creado'));
    }

    public function destroyForCompanyUser(
        User $user,
        UserEmail $userEmail,
        DeleteUserEmail $action,
    ): RedirectResponse {
        abort_unless($userEmail->user_id === $user->id, 404);
        $this->authorize('delete', $userEmail);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userEmail, $company);

        return back()->with('success', __('email_adicional_eliminado'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
