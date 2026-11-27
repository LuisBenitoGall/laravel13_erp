<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateTableFilterPreset;
use App\Domains\Admin\Actions\DeleteTableFilterPreset;
use App\Domains\Admin\Actions\UpdateTableFilterPreset;
use App\Domains\Admin\Http\Requests\StoreTableFilterPresetRequest;
use App\Domains\Admin\Http\Requests\TableFilterPresetIndexRequest;
use App\Domains\Admin\Http\Requests\UpdateTableFilterPresetRequest;
use App\Domains\Admin\Http\Resources\TableFilterPresetResource;
use App\Domains\Admin\Models\TableFilterPreset;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TableFilterPresetController extends Controller
{
    public function index(TableFilterPresetIndexRequest $request): JsonResponse
    {
        $user = $request->user();
        $tableKey = (string) $request->validated('table_key');

        $presets = TableFilterPreset::query()
            ->where('user_id', $user->id)
            ->where('table_key', $tableKey)
            ->orderBy('name')
            ->get();

        return TableFilterPresetResource::collection($presets)->response();
    }

    public function store(
        StoreTableFilterPresetRequest $request,
        CreateTableFilterPreset $action,
    ): JsonResponse {
        $preset = $action->handle($request->user(), $request->validated());

        return TableFilterPresetResource::make($preset)
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateTableFilterPresetRequest $request,
        TableFilterPreset $tableFilterPreset,
        UpdateTableFilterPreset $action,
    ): JsonResponse {
        $this->authorize('update', $tableFilterPreset);

        $preset = $action->handle($tableFilterPreset, $request->validated());

        return TableFilterPresetResource::make($preset)->response();
    }

    public function destroy(
        TableFilterPreset $tableFilterPreset,
        DeleteTableFilterPreset $action,
    ): JsonResponse {
        $this->authorize('delete', $tableFilterPreset);
        $action->handle($tableFilterPreset);

        return response()->json(null, 204);
    }
}
