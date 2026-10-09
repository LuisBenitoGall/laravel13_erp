<?php

namespace App\Domains\Partners\Http\Resources;

use App\Domains\Partners\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        ];
    }
}
