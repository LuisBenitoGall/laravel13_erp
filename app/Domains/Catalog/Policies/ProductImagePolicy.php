<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Imágenes de Product: mutaciones y stream en Edit = ProductPolicy::update.
 */
class ProductImagePolicy
{
    public function upload(User $user, Product $product): bool
    {
        return Gate::forUser($user)->allows('update', $product);
    }

    public function view(User $user, ProductImage $image): bool
    {
        $product = $image->product;

        if ($product === null) {
            return false;
        }

        return Gate::forUser($user)->allows('update', $product);
    }

    public function delete(User $user, ProductImage $image): bool
    {
        return $this->canManage($user, $image);
    }

    public function markFeatured(User $user, ProductImage $image): bool
    {
        return $this->canManage($user, $image);
    }

    public function updatePublic(User $user, ProductImage $image): bool
    {
        return $this->canManage($user, $image);
    }

    private function canManage(User $user, ProductImage $image): bool
    {
        $product = $image->product;

        if ($product === null) {
            return false;
        }

        return Gate::forUser($user)->allows('update', $product);
    }
}
