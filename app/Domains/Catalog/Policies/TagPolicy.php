<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tags.view');
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->can('tags.viewAny') && $tag->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('tags.create');
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->can('tags.update') && $tag->belongsToCurrentCompany();
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->can('tags.delete') && $tag->belongsToCurrentCompany();
    }
}
