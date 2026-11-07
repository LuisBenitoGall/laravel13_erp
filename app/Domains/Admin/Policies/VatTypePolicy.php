<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\VatType;
use App\Models\User;

class VatTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vat-types.view');
    }

    public function view(User $user, VatType $vatType): bool
    {
        return $user->can('vat-types.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('vat-types.create');
    }

    public function update(User $user, VatType $vatType): bool
    {
        return $user->can('vat-types.update');
    }

    public function delete(User $user, VatType $vatType): bool
    {
        return $user->can('vat-types.delete');
    }
}
