<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Support\MenuCatalogCache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateFunctionality
{
    /** @param  array<string, mixed>  $data */
    public function handle(Functionality $functionality, array $data): Functionality
    {
        if (array_key_exists('slug', $data) && $data['slug'] !== $functionality->slug) {
            throw new InvalidArgumentException('Functionality slug is immutable (R-AUT-07).');
        }

        return DB::transaction(function () use ($functionality, $data) {
            // name/module_id/slug inmutables desde el modal; status solo vía toggle request.
            unset($data['slug'], $data['name'], $data['module_id']);
            $functionality->update($data);

            MenuCatalogCache::forget();

            return $functionality->refresh();
        });
    }
}
