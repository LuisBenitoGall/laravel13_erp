<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductDoc;
use Illuminate\Support\Facades\DB;

class UpdateProductDocPublic
{
    public function handle(ProductDoc $doc, bool $public): ProductDoc
    {
        return DB::transaction(function () use ($doc, $public): ProductDoc {
            $doc->update(['public' => $public]);

            return $doc->refresh();
        });
    }
}
