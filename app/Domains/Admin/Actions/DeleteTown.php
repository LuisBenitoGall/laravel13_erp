<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Town;
use Illuminate\Support\Facades\DB;

class DeleteTown
{
    public function handle(Town $town): void
    {
        DB::transaction(fn () => $town->delete());
    }
}
