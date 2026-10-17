<?php

namespace App\Domains\Partners\Http\Controllers;

use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Actions\DeleteCompany;
use App\Domains\Partners\Actions\UpdateCompany;
use App\Domains\Partners\Http\Requests\CompanyTableRequest;
use App\Domains\Partners\Http\Requests\StoreCompanyRequest;
use App\Domains\Partners\Http\Requests\UpdateCompanyRequest;
use App\Domains\Partners\Http\Resources\CompanyResource;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Company::class);

        return Inertia::render('Partners/Companies/Index');
    }

    public function table(CompanyTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        return $table->for(Company::query())
            ->searchable(['name', 'slug', 'nif', 'tradename'])
            ->request($request)
            ->through(CompanyResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('create', Company::class);

        return Inertia::render('Partners/Companies/Create');
    }

    public function store(StoreCompanyRequest $request, CreateCompany $action): RedirectResponse
    {
        $company = $action->handle($request->validated(), $request->user());

        return to_route('partners.companies.edit', $company)
            ->with('success', __('empresa_creada'));
    }

    public function edit(Company $company): Response
    {
        $this->authorize('update', $company);

        return Inertia::render('Partners/Companies/Edit', [
            'company' => CompanyResource::make($company)->resolve(),
            'members_url' => route('partners.companies.members.index', $company, absolute: false),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompany $action): RedirectResponse
    {
        $action->handle($company, $request->validated());

        return back()->with('success', __('empresa_actualizada'));
    }

    public function destroy(Company $company, DeleteCompany $action): RedirectResponse
    {
        $this->authorize('delete', $company);
        $action->handle($company);

        return to_route('partners.companies.index')
            ->with('success', __('empresa_eliminada'));
    }
}
