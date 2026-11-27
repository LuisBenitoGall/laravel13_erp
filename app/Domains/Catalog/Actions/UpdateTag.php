<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Tag;
use Illuminate\Support\Facades\DB;

class UpdateTag
{
    /** @param  array<string, mixed>  $data */
    public function handle(Tag $tag, array $data): Tag
    {
        return DB::transaction(function () use ($tag, $data): Tag {
            unset($data['slug']);

            $tag->update($data);

            return $tag->refresh();
        });
    }
}
