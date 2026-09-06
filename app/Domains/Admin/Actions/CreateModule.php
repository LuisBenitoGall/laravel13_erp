<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CreateModule
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Module
    {
        return DB::transaction(function () use ($data) {
            $module = Module::query()->create($data);

            Permission::findOrCreate($module->slug.'.access');

            return $module;
        });
    }
}
