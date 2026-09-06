<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Construye el menú data-driven (R-AUT-08 parcial).
 *
 * Excepción documentada: NO se filtra por CompanyModules (aún no existe companies).
 * Solo se aplica la parte de permisos: comodín OR permiso de Functionality.
 */
class MenuBuilder
{
    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     color: string,
     *     icon: string,
     *     functionalities: list<array{slug: string, label: string, href: string}>
     * }>
     */
    public function forUser(User $user): array
    {
        /** @var Collection<int, Module> $modules */
        $modules = Module::query()
            ->where('status', ModuleStatus::Active)
            ->with(['functionalities' => fn ($q) => $q->where('status', FunctionalityStatus::Active)->orderBy('slug')])
            ->orderBy('slug')
            ->get();

        $menu = [];

        foreach ($modules as $module) {
            $hasWildcard = $user->can($module->slug.'.access');
            $items = [];

            foreach ($module->functionalities as $functionality) {
                if ($hasWildcard || $user->can($functionality->slug.'.viewAny')) {
                    $items[] = [
                        'slug' => $functionality->slug,
                        'label' => (string) $functionality->label,
                        'href' => $this->hrefFor($functionality),
                    ];
                }
            }

            if ($hasWildcard || $items !== []) {
                $menu[] = [
                    'slug' => $module->slug,
                    'label' => (string) $module->label,
                    'color' => $module->color,
                    'icon' => $module->icon,
                    'functionalities' => $hasWildcard
                        ? $module->functionalities->map(fn (Functionality $f) => [
                            'slug' => $f->slug,
                            'label' => (string) $f->label,
                            'href' => $this->hrefFor($f),
                        ])->values()->all()
                        : $items,
                ];
            }
        }

        return $menu;
    }

    private function hrefFor(Functionality $functionality): string
    {
        return match ($functionality->slug) {
            'modules' => route('admin.modules.index', absolute: false),
            'functionalities' => route('admin.functionalities.index', absolute: false),
            default => '#',
        };
    }
}
