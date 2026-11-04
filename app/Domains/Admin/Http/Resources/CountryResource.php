<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Country */
class CountryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'alfa2' => $this->alfa2,
            'alfa3' => $this->alfa3,
            'flag' => $this->flag,
            'status' => $this->status->value,
            'provinces_count' => $this->whenCounted('provinces'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
