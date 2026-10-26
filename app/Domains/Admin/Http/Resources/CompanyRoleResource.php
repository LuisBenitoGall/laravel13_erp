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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'protected_local' => $this->isLocalProtected(),
            'local_manager' => $this->isLocalManager(),
            'local_guest' => $this->isLocalGuest(),
            'permissions_readonly' => $this->isLocalManager(),
            'name_readonly' => $this->isLocalProtected(),
            'permission_names' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->values()->all(),
                [],
            ),
        ];
    }
}
