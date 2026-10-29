<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Functionality;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Functionality */
class FunctionalityResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'module' => $this->whenLoaded('module', fn () => [
                'id' => $this->module->id,
                'slug' => $this->module->slug,
                'label_text' => $this->module->label,
            ]),
            'module_name' => $this->whenLoaded(
                'module',
                fn () => (string) $this->module->label,
                '',
            ),
            'name' => $this->name,
            'slug' => $this->slug,
            'label' => $this->getTranslations('label'),
            'label_text' => $this->label,
            'status' => $this->status->value,
        ];
    }
}
