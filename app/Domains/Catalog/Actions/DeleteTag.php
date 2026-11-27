<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Tag;
use Illuminate\Support\Facades\DB;

class DeleteTag
{
    public function handle(Tag $tag): void
    {
        DB::transaction(fn () => $tag->delete());
    }
}
