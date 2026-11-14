<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Sector;
use Illuminate\Support\Facades\DB;

class DeleteSector
{
    public function handle(Sector $sector): void
    {
        DB::transaction(function () use ($sector): void {
            $sector->companies()->detach();
            $sector->delete();
        });
    }
}
