<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateMediaAsset
{
    /**
     * @param  list<UploadedFile>  $files
     * @param  array{company_id?: int|null}  $data
     * @return list<MediaAsset>
     */
    public function handle(array $files, array $data = []): array
    {
        if (array_key_exists('company_id', $data) && $data['company_id'] !== null) {
            throw ValidationException::withMessages([
                'company_id' => __('galeria_upload_empresa_bloqueado'),
            ]);
        }

        $maxPerUpload = (int) config('gallery.max_per_upload', 20);

        if (count($files) > $maxPerUpload) {
            throw ValidationException::withMessages([
                'images' => __('imagenes_limite_subida', ['max' => $maxPerUpload]),
            ]);
        }

        if ($files === []) {
            throw ValidationException::withMessages([
                'images' => __('galeria_imagen_requerida'),
            ]);
        }

        return DB::transaction(function () use ($files): array {
            $created = [];

            foreach ($files as $file) {
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

                $original = $file->getClientOriginalName();
                $baseName = pathinfo($original, PATHINFO_FILENAME);
                $name = $baseName !== '' ? $baseName : $original;

                $created[] = MediaAsset::query()->create([
                    'company_id' => null,
                    'name' => Str::limit($name, 150, ''),
                    'alt' => '',
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'width' => $width,
                    'height' => $height,
                    'bytes' => $file->getSize() ?: null,
                ]);
            }

            return $created;
        });
    }
}
