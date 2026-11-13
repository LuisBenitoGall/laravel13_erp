<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Bank */
class BankResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tradename' => $this->tradename,
            'lei' => $this->lei,
            'eu_code' => $this->eu_code,
            'supervisor_code' => $this->supervisor_code,
            'swift' => $this->swift,
            'swift_source' => $this->swift_source,
            'swift_updated_at' => $this->swift_updated_at?->toIso8601String(),
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
