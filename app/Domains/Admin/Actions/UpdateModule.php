<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;

class UpdateModule
{
    /** @param  array<string, mixed>  $data */
    public function handle(Module $module, array $data): Module
    {
        return DB::transaction(function () use ($module, $data) {
            unset($data['slug']);
            $module->update($data);

            return $module->refresh();
        });
    }
}
