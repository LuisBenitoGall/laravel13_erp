<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Preference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Preference */
class PreferenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $module = $this->relationLoaded('module') ? $this->module : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'module_id' => $this->module_id,
            'color' => $module?->color,
            'icon' => $module?->icon,
        ];
    }
}
