<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\TableFilterPreset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TableFilterPreset */
class TableFilterPresetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_key' => $this->table_key,
            'name' => $this->name,
            'filters' => $this->filters,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
