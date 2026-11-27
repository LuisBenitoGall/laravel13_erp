<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\CreateFamily;
use App\Domains\Catalog\Actions\DeleteFamily;
use App\Domains\Catalog\Actions\UpdateFamily;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Http\Requests\FamilyTableRequest;
use App\Domains\Catalog\Http\Requests\StoreFamilyRequest;
use App\Domains\Catalog\Http\Requests\UpdateFamilyRequest;
use App\Domains\Catalog\Http\Resources\FamilyResource;
use App\Domains\Catalog\Models\Family;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FamilyController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Family::class);

        return Inertia::render('Catalog/Families/Index');
    }

    public function table(FamilyTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Family::class);

        $query = Family::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, FamilyStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(FamilyResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Family::class);

        return Inertia::render('Catalog/Families/Create');
    }

    public function store(StoreFamilyRequest $request, CreateFamily $action): RedirectResponse
    {
        $family = $action->handle($request->validated());

        return to_route('products.families.edit', $family)
            ->with('success', __('familia_creada'));
    }

    public function edit(Family $family): InertiaResponse
    {
        $this->authorize('update', $family);

        return Inertia::render('Catalog/Families/Edit', [
            'family' => FamilyResource::make($family)->resolve(),
        ]);
    }

    public function update(
        UpdateFamilyRequest $request,
        Family $family,
        UpdateFamily $action,
    ): RedirectResponse {
        $action->handle($family, $request->validated());

        return back()->with('success', __('familia_actualizada'));
    }

    public function destroy(Family $family, DeleteFamily $action): RedirectResponse
    {
        $this->authorize('delete', $family);
        $action->handle($family);

        return to_route('products.families.index')
            ->with('success', __('familia_eliminada'));
    }
}
