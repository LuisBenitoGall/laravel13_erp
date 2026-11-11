<?php

namespace App\Domains\Admin\Http\Resources;

use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserCompany */
class UserCompanyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var string|null $companyName */
        $companyName = $this->resource->getAttribute('company_name');

        if ($companyName === null && $this->relationLoaded('company')) {
            $companyName = $this->company?->name;
        }

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => (string) ($companyName ?? ''),
            'position' => $this->position,
            'department' => $this->department,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
