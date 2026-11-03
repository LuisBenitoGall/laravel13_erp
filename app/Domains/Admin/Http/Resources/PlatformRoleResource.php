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
        $usersCount = isset($this->resource->users_count)
            ? (int) $this->resource->users_count
            : $this->resource->users()->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'universal' => (bool) $this->universal,
            'protected_template' => $this->isProtectedTemplate(),
            'manager_template' => $this->isManagerTemplate(),
            // Plantillas manager/guest: matriz editable; no borrables vía can_delete.
            'permissions_readonly' => false,
            'can_delete' => ! $this->isProtectedTemplate() && $usersCount === 0,
            'permission_names' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->values()->all(),
                [],
            ),
        ];
    }
}
