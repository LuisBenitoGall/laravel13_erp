<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateUserPhone;
use App\Domains\Admin\Actions\DeleteUserPhone;
use App\Domains\Admin\Actions\MarkUserPhonePrimary;
use App\Domains\Admin\Actions\UpdateUserPhone;
use App\Domains\Admin\Http\Requests\StoreUserPhoneRequest;
use App\Domains\Admin\Http\Requests\UpdateUserPhoneRequest;
use App\Domains\Admin\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\Phone;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPhoneController extends Controller
{
    public function storeForProfile(
        StoreUserPhoneRequest $request,
        CreateUserPhone $action,
    ): RedirectResponse {
        /** @var User $owner */
        $owner = $request->user();
        $this->authorize('create', [Phone::class, $owner]);

        $action->handle($owner, $request->validated());

        return back()->with('success', __('telefono_creado'));
    }

    public function updateForProfile(
        UpdateUserPhoneRequest $request,
        Phone $phone,
        UpdateUserPhone $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->phoneBelongsToUser($phone, $actor), 403);
        $this->authorize('update', $phone);

        $action->handle($phone, $request->validated());

        return back()->with('success', __('telefono_actualizado'));
    }

    public function destroyForProfile(
        Request $request,
        Phone $phone,
        DeleteUserPhone $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->phoneBelongsToUser($phone, $actor), 403);
        $this->authorize('delete', $phone);

        $action->handle($phone);

        return back()->with('success', __('telefono_eliminado'));
    }

    public function markPrimaryForProfile(
        Request $request,
        Phone $phone,
        MarkUserPhonePrimary $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->phoneBelongsToUser($phone, $actor), 403);
        $this->authorize('markPrimary', $phone);

        $action->handle($phone);

        return back()->with('success', __('telefono_marcado_primario'));
    }

    public function storeForCompanyUser(
        StoreUserPhoneRequest $request,
        User $user,
        CreateUserPhone $action,
    ): RedirectResponse {
        $this->authorize('create', [Phone::class, $user]);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($user, $request->validated(), $company);

        return back()->with('success', __('telefono_creado'));
    }

    public function updateForCompanyUser(
        UpdateUserPhoneRequest $request,
        User $user,
        Phone $phone,
        UpdateUserPhone $action,
    ): RedirectResponse {
        abort_unless($this->phoneBelongsToUser($phone, $user), 404);
        $this->authorize('update', $phone);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($phone, $request->validated(), $company);

        return back()->with('success', __('telefono_actualizado'));
    }

    public function destroyForCompanyUser(
        User $user,
        Phone $phone,
        DeleteUserPhone $action,
    ): RedirectResponse {
        abort_unless($this->phoneBelongsToUser($phone, $user), 404);
        $this->authorize('delete', $phone);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($phone, $company);

        return back()->with('success', __('telefono_eliminado'));
    }

    public function markPrimaryForCompanyUser(
        User $user,
        Phone $phone,
        MarkUserPhonePrimary $action,
    ): RedirectResponse {
        abort_unless($this->phoneBelongsToUser($phone, $user), 404);
        $this->authorize('markPrimary', $phone);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($phone, $company);

        return back()->with('success', __('telefono_marcado_primario'));
    }

    private function phoneBelongsToUser(Phone $phone, User $user): bool
    {
        return $phone->phoneable_type === $user->getMorphClass()
            && (int) $phone->phoneable_id === (int) $user->id;
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
