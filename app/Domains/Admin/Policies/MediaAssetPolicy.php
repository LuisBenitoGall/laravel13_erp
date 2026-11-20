<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gallery.view');
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('gallery.viewAny') && $this->inPerimeter($user, $mediaAsset);
    }

    public function create(User $user): bool
    {
        return $user->can('gallery.create');
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('gallery.update') && $this->inPerimeter($user, $mediaAsset);
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('gallery.delete') && $this->inPerimeter($user, $mediaAsset);
    }

    private function inPerimeter(User $user, MediaAsset $mediaAsset): bool
    {
        if ($mediaAsset->company_id === null) {
            return true;
        }

        return $user->companies()->where('companies.id', $mediaAsset->company_id)->exists();
    }
}
