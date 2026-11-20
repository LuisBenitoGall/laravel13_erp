<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateMediaAssetCopy
{
    public function handle(MediaAsset $source, UploadedFile $file): MediaAsset
    {
        return DB::transaction(function () use ($source, $file): MediaAsset {
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $path = 'gallery/'.Str::uuid()->toString().'.'.$ext;

            Storage::disk('public')->putFileAs(
                'gallery',
                $file,
                basename($path),
            );

            $width = null;
            $height = null;
            $realPath = $file->getRealPath();
            if (is_string($realPath) && $realPath !== '') {
                $info = @getimagesize($realPath);
                if (is_array($info)) {
                    $width = $info[0] ?: null;
                    $height = $info[1] ?: null;
                }
            }

            return MediaAsset::query()->create([
                'company_id' => null,
                'name' => $source->name,
                'alt' => $source->alt,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'width' => $width,
                'height' => $height,
                'bytes' => $file->getSize() ?: null,
            ]);
        });
    }
}
