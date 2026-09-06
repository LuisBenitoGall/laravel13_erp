<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
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
            unset($data['slug']);
            $functionality->update($data);

            return $functionality->refresh();
        });
    }
}
