<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;

class DeleteModule
{
    public function handle(Module $module): void
    {
        DB::transaction(fn () => $module->delete());
    }
}
