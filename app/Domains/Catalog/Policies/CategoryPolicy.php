<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('categories.viewAny') && $category->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('categories.update') && $category->belongsToCurrentCompany();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('categories.delete') && $category->belongsToCurrentCompany();
    }
}
