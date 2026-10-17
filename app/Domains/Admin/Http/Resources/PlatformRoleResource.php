<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
class PlatformRoleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'universal' => (bool) $this->universal,
            'protected_template' => $this->isProtectedTemplate(),
            'manager_template' => $this->isManagerTemplate(),
            'permissions_readonly' => $this->isManagerTemplate(),
            'permission_names' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->values()->all(),
                [],
            ),
        ];
    }
}
