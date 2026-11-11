<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DetachUserCompany;
use App\Domains\Admin\Actions\UpdateUserCompany;
use App\Domains\Admin\Http\Requests\UpdateUserCompanyRequest;
use App\Domains\Admin\Http\Requests\UserCompanyTableRequest;
use App\Domains\Admin\Http\Resources\UserCompanyResource;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCompany;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCompanyController extends Controller
{
    public function tableForProfile(
        UserCompanyTableRequest $request,
        TableBuilder $table,
    ): JsonResponse {
        /** @var User $owner */
        $owner = $request->user();
        $this->authorize('viewAny', [UserCompany::class, $owner]);

        return $this->membershipTable($table, $request, $owner);
    }

    public function updateForProfile(
        UpdateUserCompanyRequest $request,
        UserCompany $userCompany,
        UpdateUserCompany $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userCompany->user_id === $actor->id, 403);
        $this->authorize('update', $userCompany);

        $action->handle($userCompany, $request->validated());

        return redirect()
            ->route('profile.show', ['tab' => 'companies'])
            ->with('success', __('vinculo_empresa_actualizado'));
    }

    public function destroyForProfile(
        Request $request,
        UserCompany $userCompany,
        DetachUserCompany $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userCompany->user_id === $actor->id, 403);
        $this->authorize('delete', $userCompany);

        return $this->detachAndRespond(
            $request,
            $userCompany,
            $actor,
            $action,
            route('profile.show', ['tab' => 'companies']),
        );
    }

    public function tableForCompanyUser(
        UserCompanyTableRequest $request,
        User $user,
        TableBuilder $table,
    ): JsonResponse {
        $this->authorize('viewAny', [UserCompany::class, $user]);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        return $this->membershipTable($table, $request, $user);
    }

    public function updateForCompanyUser(
        UpdateUserCompanyRequest $request,
        User $user,
        UserCompany $userCompany,
        UpdateUserCompany $action,
    ): RedirectResponse {
        abort_unless($userCompany->user_id === $user->id, 404);
        $this->authorize('update', $userCompany);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userCompany, $request->validated());

        return redirect()
            ->route('admin.company-users.edit', ['user' => $user, 'tab' => 'companies'])
            ->with('success', __('vinculo_empresa_actualizado'));
    }

    public function destroyForCompanyUser(
        Request $request,
        User $user,
        UserCompany $userCompany,
        DetachUserCompany $action,
    ): RedirectResponse {
        abort_unless($userCompany->user_id === $user->id, 404);
        $this->authorize('delete', $userCompany);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        /** @var User $actor */
        $actor = $request->user();

        return $this->detachAndRespond(
            $request,
            $userCompany,
            $actor,
            $action,
            route('admin.company-users.edit', ['user' => $user, 'tab' => 'companies']),
        );
    }

    private function membershipTable(
        TableBuilder $table,
        UserCompanyTableRequest $request,
        User $owner,
    ): JsonResponse {
        return $table->for(
            UserCompany::query()
                ->select('user_companies.*')
                ->join('companies', 'companies.id', '=', 'user_companies.company_id')
                ->where('user_companies.user_id', $owner->id)
                ->with('company')
                ->orderByDesc('user_companies.id'),
        )
            ->searchable(['companies.name', 'user_companies.position', 'user_companies.department'])
            ->request($request)
            ->through(UserCompanyResource::class)
            ->json();
    }

    private function detachAndRespond(
        Request $request,
        UserCompany $membership,
        User $actor,
        DetachUserCompany $action,
        string $successUrl,
    ): RedirectResponse {
        $result = $action->handle($membership, $actor, $request);

        if ($result['logout']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('success', __('vinculo_empresa_eliminado'));
        }

        return redirect()
            ->to($successUrl)
            ->with('success', __('vinculo_empresa_eliminado'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
