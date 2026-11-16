<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Preference;
use Illuminate\Support\Facades\DB;

class DeletePreference
{
    public function handle(Preference $preference): void
    {
        DB::transaction(fn () => $preference->delete());
    }
}
