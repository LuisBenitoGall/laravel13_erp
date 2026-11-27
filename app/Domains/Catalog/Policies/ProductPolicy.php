<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalogue.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('catalogue.viewAny') && $product->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('catalogue.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('catalogue.update') && $product->belongsToCurrentCompany();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('catalogue.delete') && $product->belongsToCurrentCompany();
    }
}
