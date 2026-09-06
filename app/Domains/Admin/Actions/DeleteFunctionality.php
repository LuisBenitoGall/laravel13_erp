<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use Illuminate\Support\Facades\DB;

class DeleteFunctionality
{
    public function handle(Functionality $functionality): void
    {
        DB::transaction(fn () => $functionality->delete());
    }
}
