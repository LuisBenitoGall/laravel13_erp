<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Workplace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Workplace */
class DelegationLinkedWorkplaceResource extends JsonResource
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
            'cp' => $this->cp,
            'town_name' => $town?->name,
            'province_name' => $province?->name,
            'country_name' => $country?->name,
            'created_at' => $this->created_at?->toIso8601String(),
            'is_preferred_seat' => (bool) $this->getAttribute('is_preferred_seat'),
        ];
    }
}
