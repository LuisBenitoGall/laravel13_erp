<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Company */
class CompanyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tradename' => $this->tradename,
            'nif' => $this->nif,
            'status' => $this->status->value,
            'logo' => $this->logo,
            'logo_url' => $this->logo
                ? Storage::disk('public')->url($this->logo)
                : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
