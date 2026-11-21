<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CompanyAccount */
class CompanyAccountResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account_name' => $this->whenLoaded('account', fn () => $this->account?->name),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'price' => $this->price,
            'payment_date' => $this->payment_date?->toDateString(),
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
