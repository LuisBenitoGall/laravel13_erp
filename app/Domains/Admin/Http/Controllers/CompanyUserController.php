<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DetachCompanyUser;
use App\Domains\Admin\Actions\UpdateCompanyUserStatus;
use App\Domains\Admin\Http\Requests\CompanyUserTableRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyUserStatusRequest;
use App\Domains\Admin\Http\Resources\DirectoryUserResource;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyUserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAnyCompanyUser');

        return Inertia::render('Admin/CompanyUsers/Index');
    }

    public function table(CompanyUserTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAnyCompanyUser');
        $company = $this->currentCompany();

        return $table->for(
            User::query()->whereHas(
                'companies',
                fn ($query) => $query->where('companies.id', $company->id),
            ),
        )
            ->searchable(['name', 'surname', 'email'])
            ->request($request)
            ->through(DirectoryUserResource::class)
            ->json();
    }

    public function updateStatus(
        UpdateCompanyUserStatusRequest $request,
        User $user,
        UpdateCompanyUserStatus $action,
    ): JsonResponse {
        $this->authorize('updateCompanyUser', $user);
        $company = $this->currentCompany();

        $member = $action->handle(
            $company,
            $user,
            (bool) $request->validated('status'),
        );

        return response()->json([
            'status' => (bool) $member->status,
        ]);
    }

    public function destroy(User $user, DetachCompanyUser $action): RedirectResponse
    {
        $this->authorize('deleteCompanyUser', $user);
        $company = $this->currentCompany();

        $action->handle($company, $user);

        return back()->with('success', __('usuario_desvinculado_empresa'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
