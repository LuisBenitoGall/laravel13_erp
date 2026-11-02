<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Support\CanonicalPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/** @mixin Permission */
class PermissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $slug = CanonicalPermission::functionalitySlug((string) $this->name);
        $labels = $this->functionalityLabels();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'functionality_slug' => $slug,
            'functionality_label' => $slug !== null
                ? ($labels->get($slug) ?? $slug)
                : '—',
            'can_delete' => ! CanonicalPermission::isCanonical((string) $this->name),
        ];
    }

    /** @return Collection<string, string> */
    private function functionalityLabels(): Collection
    {
        return once(fn (): Collection => Functionality::query()
            ->get(['slug', 'label'])
            ->mapWithKeys(fn (Functionality $functionality): array => [
                $functionality->slug => (string) $functionality->label,
            ]));
    }
}
