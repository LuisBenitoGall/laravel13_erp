<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Module */
class ModuleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'label' => $this->getTranslations('label'),
            'label_text' => $this->label,
            'color' => $this->color,
            'icon' => $this->icon,
            'level' => $this->level->value,
            'status' => $this->status->value,
            'explanation' => $this->getTranslations('explanation'),
        ];
    }
}
