<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Content */
class ContentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'kind' => $this->kind->value,
            'company_id' => $this->company_id,
            'title' => $this->title,
            'titles' => $this->getTranslations('title'),
            'excerpt' => $this->excerpt,
            'excerpts' => $this->getTranslations('excerpt'),
            'content' => $this->content,
            'contents' => $this->getTranslations('content'),
            'referrer' => $this->referrer,
            'video' => $this->video,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
