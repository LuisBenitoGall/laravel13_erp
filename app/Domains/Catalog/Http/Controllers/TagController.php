<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\CreateTag;
use App\Domains\Catalog\Actions\DeleteTag;
use App\Domains\Catalog\Actions\UpdateTag;
use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Http\Requests\StoreTagRequest;
use App\Domains\Catalog\Http\Requests\TagTableRequest;
use App\Domains\Catalog\Http\Requests\UpdateTagRequest;
use App\Domains\Catalog\Http\Resources\TagResource;
use App\Domains\Catalog\Models\Tag;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TagController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Tag::class);

        return Inertia::render('Catalog/Tags/Index');
    }

    public function table(TagTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Tag::class);

        $query = Tag::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, TagStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(TagResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Tag::class);

        return Inertia::render('Catalog/Tags/Create');
    }

    public function store(StoreTagRequest $request, CreateTag $action): RedirectResponse
    {
        $tag = $action->handle($request->validated());

        return to_route('products.tags.edit', $tag)
            ->with('success', __('etiqueta_creada'));
    }

    public function edit(Tag $tag): InertiaResponse
    {
        $this->authorize('update', $tag);

        return Inertia::render('Catalog/Tags/Edit', [
            'tag' => TagResource::make($tag)->resolve(),
        ]);
    }

    public function update(
        UpdateTagRequest $request,
        Tag $tag,
        UpdateTag $action,
    ): RedirectResponse {
        $action->handle($tag, $request->validated());

        return back()->with('success', __('etiqueta_actualizada'));
    }

    public function destroy(Tag $tag, DeleteTag $action): RedirectResponse
    {
        $this->authorize('delete', $tag);
        $action->handle($tag);

        return to_route('products.tags.index')
            ->with('success', __('etiqueta_eliminada'));
    }
}
