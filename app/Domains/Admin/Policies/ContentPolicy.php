<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contents.view');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->can('contents.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('contents.create');
    }

    public function update(User $user, Content $content): bool
    {
        return $user->can('contents.update');
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->can('contents.delete');
    }
}
