<?php

namespace App\Domains\Catalog\Http\Resources;

use App\Domains\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'family_id' => $this->family_id,
            'category_ids' => $this->whenLoaded(
                'categories',
                fn () => $this->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                [],
            ),
            'tag_ids' => $this->whenLoaded(
                'tags',
                fn () => $this->tags->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                [],
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
