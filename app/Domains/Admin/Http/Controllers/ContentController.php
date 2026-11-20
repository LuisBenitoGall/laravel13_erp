<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateContent;
use App\Domains\Admin\Actions\DeleteContent;
use App\Domains\Admin\Actions\ToggleContentStatus;
use App\Domains\Admin\Actions\UpdateContent;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Http\Requests\ContentTableRequest;
use App\Domains\Admin\Http\Requests\StoreContentRequest;
use App\Domains\Admin\Http\Requests\ToggleContentStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateContentRequest;
use App\Domains\Admin\Http\Resources\ContentResource;
use App\Domains\Admin\Models\Content;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContentController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Content::class);

        return Inertia::render('Admin/Contents/Index');
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Content::class);

        return Inertia::render('Admin/Contents/Create');
    }

    public function edit(Content $content): InertiaResponse
    {
        $this->authorize('update', $content);

        return Inertia::render('Admin/Contents/Edit', [
            'content' => ContentResource::make($content)->resolve(),
        ]);
    }

    public function table(ContentTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Content::class);

        $query = Content::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, ContentStatus::Active);
            $query->orderBy('name');
        }

        return $table->for($query)
            ->searchable(['name', 'code'])
            ->request($request)
            ->through(ContentResource::class)
            ->json();
    }

    public function store(StoreContentRequest $request, CreateContent $action): RedirectResponse
    {
        $content = $action->handle($request->validated());

        return to_route('admin.contents.edit', $content)
            ->with('success', __('contenido_creado'));
    }

    public function update(
        UpdateContentRequest $request,
        Content $content,
        UpdateContent $action,
    ): RedirectResponse {
        $action->handle($content, $request->validated());

        return to_route('admin.contents.edit', $content)
            ->with('success', __('contenido_actualizado'));
    }

    public function updateStatus(
        ToggleContentStatusRequest $request,
        Content $content,
        ToggleContentStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $content = $action->handle(
            $content,
            is_string($status) ? ContentStatus::from($status) : null,
        );

        return response()->json([
            'status' => $content->status->value,
        ]);
    }

    public function destroy(Content $content, DeleteContent $action): RedirectResponse
    {
        $this->authorize('delete', $content);
        $action->handle($content);

        return to_route('admin.contents.index')
            ->with('success', __('contenido_eliminado'));
    }
}
