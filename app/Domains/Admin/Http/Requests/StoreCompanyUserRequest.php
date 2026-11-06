<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Role;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'status' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'send_credentials' => ['required', 'boolean'],
            'role' => ['required', 'string', 'max:255', Rule::in($this->allowedRoleNames())],
        ];
    }

    /** @return list<string> */
    private function allowedRoleNames(): array
    {
        $companyId = app(CurrentCompany::class)->id();
        if ($companyId === null) {
            return [];
        }

        $names = Role::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            $universals = Role::query()
                ->universalTemplates()
                ->orderBy('name')
                ->pluck('name')
                ->all();
        } finally {
            setPermissionsTeamId($previousTeamId);
        }

        $names = array_values(array_unique([...$names, ...$universals]));

        if ($this->actorIsSuperAdmin()) {
            $names[] = Role::SUPER_ADMIN;
        }

        return $names;
    }

    private function actorIsSuperAdmin(): bool
    {
        $actor = $this->user();
        if (! $actor instanceof User) {
            return false;
        }

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');

            return $actor->hasRole(Role::SUPER_ADMIN);
        } finally {
            setPermissionsTeamId($previousTeamId);
            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');
        }
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
            'is_public' => $this->boolean('is_public'),
            'send_credentials' => $this->boolean('send_credentials'),
        ]);
    }
}
