<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDoc;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Documentos de Product: mutaciones y descarga en Edit = ProductPolicy::update.
 */
class ProductDocPolicy
{
    public function upload(User $user, Product $product): bool
    {
        return Gate::forUser($user)->allows('update', $product);
    }

    public function download(User $user, ProductDoc $doc): bool
    {
        return $this->canManage($user, $doc);
    }

    public function delete(User $user, ProductDoc $doc): bool
    {
        return $this->canManage($user, $doc);
    }

    public function updatePublic(User $user, ProductDoc $doc): bool
    {
        return $this->canManage($user, $doc);
    }

    private function canManage(User $user, ProductDoc $doc): bool
    {
        $product = $doc->product;

        if ($product === null) {
            return false;
        }

        return Gate::forUser($user)->allows('update', $product);
    }
}
