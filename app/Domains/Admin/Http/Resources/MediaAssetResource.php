<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MediaAsset */
class MediaAssetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'alt' => $this->alt,
            'url' => $this->url(),
            'mime' => $this->mime,
            'width' => $this->width,
            'height' => $this->height,
            'bytes' => $this->bytes,
        ];
    }
}
