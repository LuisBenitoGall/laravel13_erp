<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\AttachCompanyChild;
use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Actions\DeleteCompany;
use App\Domains\Admin\Actions\DetachCompanyChild;
use App\Domains\Admin\Actions\SyncCompanySectors;
use App\Domains\Admin\Actions\UpdateCompany;
use App\Domains\Admin\Actions\UpdateCompanyParent;
use App\Domains\Admin\Actions\UpdateCompanyStatus;
use App\Domains\Admin\Actions\UploadCompanyLogo;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Http\Requests\AttachCompanyChildRequest;
use App\Domains\Admin\Http\Requests\CompanyTableRequest;
use App\Domains\Admin\Http\Requests\DetachCompanyChildRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyRequest;
use App\Domains\Admin\Http\Requests\SyncCompanySectorsRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyParentRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyStatusRequest;
use App\Domains\Admin\Http\Requests\UploadCompanyLogoRequest;
use App\Domains\Admin\Http\Resources\CompanyResource;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Sector;
use App\Domains\Admin\Support\CompanyHierarchyOptions;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\I18n\LocaleResolver;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

    public function store(
        StoreCompanyRequest $request,
        CreateCompany $action,
        SetCompanySession $setCompanySession,
    ): RedirectResponse {
        $company = $action->handle($request->validated(), $request->user());

        $setCompanySession->handle($request, $request->user(), (int) $company->id);
        $request->session()->put('company_setup.show_modal', true);

        return to_route('admin.companies.edit', $company)
            ->with('success', __('empresa_creada'));
    }

    public function edit(
        Company $company,
        CompanyHierarchyOptions $hierarchyOptions,
        LocaleResolver $locales,
    ): Response {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        /** @var User $user */
        $user = request()->user();

        $company->load(['parent:id,name', 'children:id,name,parent_id', 'sectors']);

        $locale = $locales->resolve(request());
        $sectorOptionsQuery = Sector::query()->where('status', SectorStatus::Active);
        if (DB::getDriverName() === 'sqlite') {
            $sectorOptionsQuery->orderByRaw("json_extract(name, '$.{$locale}')");
        } else {
            $sectorOptionsQuery->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.{$locale}'))",
            );
        }

        $sectorOptions = $sectorOptionsQuery
            ->get(['id', 'name', 'slug'])
            ->map(fn (Sector $sector): array => [
                'id' => $sector->id,
                'name' => $sector->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Companies/Edit', [
            'company' => CompanyResource::make($company)->resolve(),
            'parent' => $company->parent
                ? ['id' => $company->parent->id, 'name' => $company->parent->name]
                : null,
            'children' => $company->children
                ->map(fn (Company $child): array => [
                    'id' => $child->id,
                    'name' => $child->name,
                ])
                ->values()
                ->all(),
            'hierarchy_options' => $hierarchyOptions->forEdit($company, $user),
            // Solo Active: el panel no reenvía Inactive pivoteados (design Risks).
            'sectors' => $company->sectors
                ->filter(fn (Sector $sector): bool => $sector->status === SectorStatus::Active)
                ->map(fn (Sector $sector): array => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                ])
                ->values()
                ->all(),
            'sector_options' => $sectorOptions,
            'can' => [
                'view_company_sectors' => $user->can('company-sectors.view'),
                'update_company_sectors' => $user->can('company-sectors.update'),
            ],
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompany $action): RedirectResponse
    {
        $this->assertLinked($company);
        $this->authorize('update', $company);
        $action->handle($company, $request->validated());

        return back()->with('success', __('empresa_actualizada'));
    }

    public function updateParent(
        UpdateCompanyParentRequest $request,
        Company $company,
        UpdateCompanyParent $action,
    ): RedirectResponse {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        $parentId = $request->validated('parent_id');
        $parent = $parentId !== null
            ? Company::query()->findOrFail((int) $parentId)
            : null;

        if ($parent !== null) {
            $this->assertLinked($parent);
            $this->authorize('update', $parent);
        }

        $action->handle($company, $parent);

        return back()->with('success', __('jerarquia_padre_actualizado'));
    }

    public function attachChild(
        AttachCompanyChildRequest $request,
        Company $company,
        AttachCompanyChild $action,
    ): RedirectResponse {
        $this->assertLinked($company);
        $this->authorize('update', $company);

        $child = Company::query()->findOrFail((int) $request->validated('child_id'));
        $this->assertLinked($child);
        $this->authorize('update', $child);

        $action->handle($company, $child);

        return back()->with('success', __('jerarquia_hijo_anadido'));
    }

    public function detachChild(
        DetachCompanyChildRequest $request,
        Company $company,
        Company $child,
        DetachCompanyChild $action,
    ): RedirectResponse {
        $this->assertLinked($company);
        $this->assertLinked($child);
        $this->authorize('update', $company);
        $this->authorize('update', $child);

        $action->handle($company, $child);

        return back()->with('success', __('jerarquia_hijo_quitado'));
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

    public function syncSectors(
        SyncCompanySectorsRequest $request,
        Company $company,
        SyncCompanySectors $action,
    ): RedirectResponse {
        $this->assertLinked($company);

        /** @var list<int|string> $sectorIds */
        $sectorIds = $request->validated('sector_ids');
        $action->handle($company, $sectorIds);

        return back()->with('success', __('sectores_actualizados'));
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
