<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Actions\DeleteCompany;
use App\Domains\Admin\Actions\UpdateCompany;
use App\Domains\Admin\Actions\UpdateCompanyStatus;
use App\Domains\Admin\Actions\UploadCompanyLogo;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Http\Requests\CompanyTableRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyStatusRequest;
use App\Domains\Admin\Http\Requests\UploadCompanyLogoRequest;
use App\Domains\Admin\Http\Resources\CompanyResource;
use App\Domains\Admin\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Company::class);

        return Inertia::render('Admin/Companies/Index', [
            'operator' => $this->isSuperAdmin(),
        ]);
    }

    public function table(CompanyTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        /** @var User $user */
        $user = $request->user();

        return $table->for(Company::query()->forUser($user))
            ->searchable(['name', 'nif', 'tradename'])
            ->request($request)
            ->through(CompanyResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('create', Company::class);

        return Inertia::render('Admin/Companies/Create');
    }

    public function store(StoreCompanyRequest $request, CreateCompany $action): RedirectResponse
    {
        $company = $action->handle($request->validated(), $request->user());

        return to_route('admin.companies.edit', $company)
            ->with('success', __('empresa_creada'));
    }

    public function edit(Company $company): Response
    {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        return Inertia::render('Admin/Companies/Edit', [
            'company' => CompanyResource::make($company)->resolve(),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompany $action): RedirectResponse
    {
        $this->assertLinked($company);
        $this->authorize('update', $company);
        $action->handle($company, $request->validated());

        return back()->with('success', __('empresa_actualizada'));
    }

    public function uploadLogo(
        UploadCompanyLogoRequest $request,
        Company $company,
        UploadCompanyLogo $action,
    ): RedirectResponse {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        /** @var UploadedFile $file */
        $file = $request->file('logo');
        $action->handle($company, $file);

        return back()->with('success', __('logo_actualizado'));
    }

    public function updateStatus(
        UpdateCompanyStatusRequest $request,
        Company $company,
        UpdateCompanyStatus $action,
    ): JsonResponse {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        /** @var string $statusValue */
        $statusValue = $request->validated('status');
        $company = $action->handle($company, CompanyStatus::from($statusValue));

        return response()->json([
            'status' => $company->status->value,
        ]);
    }

    public function destroy(Company $company, DeleteCompany $action): RedirectResponse
    {
        $this->assertLinked($company);
        $this->authorize('delete', $company);
        $action->handle($company);

        return to_route('admin.companies.index')
            ->with('success', __('empresa_eliminada'));
    }

    private function assertLinked(Company $company): void
    {
        $user = request()->user();
        abort_unless($user instanceof User && $company->isLinkedTo($user), 403);
    }

    private function isSuperAdmin(): bool
    {
        $user = request()->user();

        if ($user === null) {
            return false;
        }

        $previous = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            return $user->hasRole('Super Admin');
        } finally {
            setPermissionsTeamId($previous);
        }
    }
}
