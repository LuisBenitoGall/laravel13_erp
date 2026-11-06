<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
class CompanyRoleResource extends JsonResource
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
            'protected_local' => $this->isLocalProtected(),
            'local_manager' => $this->isLocalManager(),
            'local_guest' => $this->isLocalGuest(),
            'permissions_readonly' => $this->isLocalManager(),
            'name_readonly' => true,
            'can_edit' => ! (bool) $this->universal,
            'can_delete' => ! $this->universal && ! $this->isLocalProtected() && $usersCount === 0,
            'permission_names' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->values()->all(),
                [],
            ),
        ];
    }
}
