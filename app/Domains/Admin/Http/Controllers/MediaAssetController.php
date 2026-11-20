<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateMediaAsset;
use App\Domains\Admin\Actions\CreateMediaAssetCopy;
use App\Domains\Admin\Actions\DeleteMediaAsset;
use App\Domains\Admin\Actions\UpdateMediaAsset;
use App\Domains\Admin\Http\Requests\StoreMediaAssetCopyRequest;
use App\Domains\Admin\Http\Requests\StoreMediaAssetRequest;
use App\Domains\Admin\Http\Requests\UpdateMediaAssetRequest;
use App\Domains\Admin\Http\Resources\MediaAssetResource;
use App\Domains\Admin\Models\MediaAsset;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MediaAssetController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', MediaAsset::class);

        /** @var User $user */
        $user = request()->user();

        $assets = $this->visibleAssetsQueryFor($user)->get();

        return Inertia::render('Admin/Gallery/Index', [
            'assets' => MediaAssetResource::collection($assets)->resolve(),
            'maxKilobytes' => (int) config('gallery.max_kilobytes', 1024),
            'maxPerUpload' => (int) config('gallery.max_per_upload', 20),
            'can' => [
                'create' => $user->can('create', MediaAsset::class),
                'update' => $user->can('gallery.update'),
                'delete' => $user->can('gallery.delete'),
            ],
        ]);
    }

    public function picker(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MediaAsset::class);

        /** @var User $user */
        $user = $request->user();

        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 24);
        $perPage = max(1, min(48, $perPage === 0 ? 24 : $perPage));

        $query = $this->visibleAssetsQueryFor($user);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $inner) use ($like): void {
                $inner->where('name', 'like', $like)
                    ->orWhere('alt', 'like', $like);
            });
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        /** @var Collection<int, MediaAsset> $items */
        $items = $paginator->getCollection();

        $assets = $items->map(static fn (MediaAsset $asset): array => [
            'id' => $asset->id,
            'name' => $asset->name,
            'alt' => $asset->alt,
            'url' => $asset->url(),
        ])->values();

        return response()->json([
            'assets' => $assets,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreMediaAssetRequest $request, CreateMediaAsset $action): RedirectResponse
    {
        /** @var list<UploadedFile> $files */
        $files = array_values($request->file('images', []));

        $action->handle($files);

        return to_route('admin.gallery.index')
            ->with('success', __('galeria_imagen_subida'));
    }

    public function storeCopy(
        StoreMediaAssetCopyRequest $request,
        MediaAsset $media_asset,
        CreateMediaAssetCopy $action,
    ): RedirectResponse {
        /** @var UploadedFile $file */
        $file = $request->file('image');

        $action->handle($media_asset, $file);

        return to_route('admin.gallery.index')
            ->with('success', __('galeria_copia_creada'));
    }

    public function update(
        UpdateMediaAssetRequest $request,
        MediaAsset $media_asset,
        UpdateMediaAsset $action,
    ): RedirectResponse {
        $action->handle($media_asset, $request->validated());

        return to_route('admin.gallery.index')
            ->with('success', __('galeria_imagen_actualizada'));
    }

    public function destroy(MediaAsset $media_asset, DeleteMediaAsset $action): RedirectResponse
    {
        $this->authorize('delete', $media_asset);

        $action->handle($media_asset);

        return to_route('admin.gallery.index')
            ->with('success', __('imagen_eliminada'));
    }

    /** @return Builder<MediaAsset> */
    private function visibleAssetsQueryFor(User $user): Builder
    {
        $companyIds = $user->companies()->pluck('companies.id');

        return MediaAsset::query()
            ->where(function (Builder $query) use ($companyIds): void {
                $query->whereNull('company_id');
                if ($companyIds->isNotEmpty()) {
                    $query->orWhereIn('company_id', $companyIds);
                }
            })
            ->orderByDesc('id');
    }
}
