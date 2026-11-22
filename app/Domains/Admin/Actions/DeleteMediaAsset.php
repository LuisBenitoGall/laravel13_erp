<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteMediaAsset
{
    public function handle(MediaAsset $asset): void
    {
        DB::transaction(function () use ($asset): void {
            $path = $asset->path;

            $asset->delete();

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        });
    }
}
