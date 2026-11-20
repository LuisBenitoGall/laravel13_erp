<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Support\Facades\DB;

class UpdateMediaAsset
{
    /**
     * @param  array{name: string, alt?: string}  $data
     */
    public function handle(MediaAsset $asset, array $data): MediaAsset
    {
        return DB::transaction(function () use ($asset, $data): MediaAsset {
            $asset->update([
                'name' => (string) $data['name'],
                'alt' => (string) ($data['alt'] ?? ''),
            ]);

            return $asset->refresh();
        });
    }
}
