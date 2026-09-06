<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CreateFunctionality
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Functionality
    {
        return DB::transaction(function () use ($data) {
            $functionality = Functionality::query()->create($data);
            $functionality->load('module');

            foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
                Permission::findOrCreate("{$functionality->slug}.{$ability}");
            }

            Permission::findOrCreate($functionality->module->slug.'.access');

            return $functionality;
        });
    }
}
