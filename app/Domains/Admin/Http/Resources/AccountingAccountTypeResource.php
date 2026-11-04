<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AccountingAccountType */
class AccountingAccountTypeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'autoreference' => $this->autoreference,
            'code' => $this->code,
            'name' => $this->name,
            'mode' => $this->mode,
            'level' => $this->level,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
