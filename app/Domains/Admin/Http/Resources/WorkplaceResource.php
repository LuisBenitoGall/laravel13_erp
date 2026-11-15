<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Workplace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Workplace */
class WorkplaceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $town = $this->town;
        $province = $town?->province;
        $country = $province?->country;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'cp' => $this->cp,
            'town_id' => $this->town_id,
            'town_name' => $town?->name,
            'province_id' => $province?->id,
            'country_id' => $country?->id,
            'featured' => (bool) $this->featured,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
