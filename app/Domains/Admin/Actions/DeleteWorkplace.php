<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Workplace;
use Illuminate\Support\Facades\DB;

class DeleteWorkplace
{
    public function handle(Workplace $workplace): void
    {
        DB::transaction(fn () => $workplace->delete());
    }
}
