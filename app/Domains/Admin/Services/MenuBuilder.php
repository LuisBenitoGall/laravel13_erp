<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Collection;

/**
 * Menú data-driven (R-AUT-08): Active + autorizado + encendido + permisos.
 */
class MenuBuilder
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

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
        $companyId = $this->currentCompany->id();

        if ($companyId === null) {
            return [];
        }

        $authorizedIds = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id');

        $enabledIds = CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id');

        $eligibleIds = $authorizedIds->intersect($enabledIds)->values();

        if ($eligibleIds->isEmpty()) {
            return [];
        }

        /** @var Collection<int, Module> $modules */
        $modules = Module::query()
            ->where('status', ModuleStatus::Active)
            ->whereIn('id', $eligibleIds)
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
            'companies' => route('partners.companies.index', absolute: false),
            'company-modules' => route('admin.company-modules.index', absolute: false),
            'company-module-authorizations' => route('admin.company-module-authorizations.index', absolute: false),
            default => '#',
        };
    }
}
