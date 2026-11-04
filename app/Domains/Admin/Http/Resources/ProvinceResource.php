<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Province */
class ProvinceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country_id' => $this->country_id,
            'status' => $this->status->value,
            'towns_count' => $this->whenCounted('towns'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
