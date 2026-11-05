<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Support\MenuCatalogCache;
use Illuminate\Support\Facades\DB;

class DeleteFunctionality
{
    public function handle(Functionality $functionality): void
    {
        DB::transaction(function () use ($functionality): void {
            $functionality->delete();
            MenuCatalogCache::forget();
        });
    }
}
