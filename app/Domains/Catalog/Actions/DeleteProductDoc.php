<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductDoc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProductDoc
{
    public function handle(ProductDoc $doc): void
    {
        DB::transaction(function () use ($doc): void {
            $path = $doc->path;
            $doc->delete();

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        });
    }
}
