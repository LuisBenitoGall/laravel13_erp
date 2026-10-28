<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Collection;

/**
 * Menú data-driven (R-AUT-08) + excepciones shell: Dashboard fijo; Configuración SA.
 */
class MenuBuilder
{
    /**
     * Mapa estático functionality slug → grupo de menú tenant (cuenta|empresa).
     * Sin entrada = permanece bajo el Module de origen.
     *
     * @var array<string, string>
     */
    private const FUNCTIONALITY_GROUPS = [
        'company-roles' => 'empresa',
    ];

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

        $menu = [$this->dashboardItem()];

        $authorizedIds = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id');

        $enabledIds = CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id');

        $eligibleIds = $authorizedIds->intersect($enabledIds)->values();

        if ($eligibleIds->isNotEmpty()) {
            /** @var Collection<int, Module> $modules */
            $modules = Module::query()
                ->where('status', ModuleStatus::Active)
                ->whereIn('level', [ModuleLevel::Basic, ModuleLevel::Optional])
                ->whereIn('id', $eligibleIds)
                ->with(['functionalities' => fn ($q) => $q->where('status', FunctionalityStatus::Active)->orderBy('slug')])
                ->orderBy('slug')
                ->get();

            foreach ($modules as $module) {
                foreach ($this->tenantModuleEntries($user, $module) as $entry) {
                    $menu[] = $entry;
                }
            }
        }

        if ($this->isSuperAdmin($user)) {
            $menu[] = $this->configurationBlock();
        }

        return $menu;
    }

    /**
     * @return array{slug: string, label: string, color: string, icon: string, functionalities: list<array{slug: string, label: string, href: string}>}
     */
    private function dashboardItem(): array
    {
        return [
            'slug' => 'dashboard',
            'label' => __('dashboard'),
            'color' => '#0f172a',
            'icon' => 'home',
            'functionalities' => [
                [
                    'slug' => 'dashboard',
                    'label' => __('dashboard'),
                    'href' => route('dashboard', absolute: false),
                ],
            ],
        ];
    }

    /**
     * @return list<array{slug: string, label: string, color: string, icon: string, functionalities: list<array{slug: string, label: string, href: string}>}>
     */
    private function tenantModuleEntries(User $user, Module $module): array
    {
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

        if (! $hasWildcard && $items === []) {
            return [];
        }

        if ($hasWildcard) {
            $items = $module->functionalities->map(fn (Functionality $f) => [
                'slug' => $f->slug,
                'label' => (string) $f->label,
                'href' => $this->hrefFor($f),
            ])->values()->all();
        }

        return $this->groupTenantItems($items, $module);
    }

    /**
     * @param  list<array{slug: string, label: string, href: string}>  $items
     * @return list<array{slug: string, label: string, color: string, icon: string, functionalities: list<array{slug: string, label: string, href: string}>}>
     */
    private function groupTenantItems(array $items, Module $module): array
    {
        $buckets = [];

        foreach ($items as $item) {
            $group = self::FUNCTIONALITY_GROUPS[$item['slug']] ?? 'default';
            $buckets[$group][] = $item;
        }

        $entries = [];

        foreach ($buckets as $group => $groupItems) {
            if ($group === 'default') {
                $entries[] = [
                    'slug' => $module->slug,
                    'label' => (string) $module->label,
                    'color' => $module->color,
                    'icon' => $module->icon,
                    'functionalities' => $groupItems,
                ];

                continue;
            }

            $entries[] = [
                'slug' => $group,
                'label' => __($group),
                'color' => $module->color,
                'icon' => $module->icon,
                'functionalities' => $groupItems,
            ];
        }

        return $entries;
    }

    /**
     * @return array{slug: string, label: string, color: string, icon: string, functionalities: list<array{slug: string, label: string, href: string}>}
     */
    private function configurationBlock(): array
    {
        /** @var Collection<int, Module> $modules */
        $modules = Module::query()
            ->where('status', ModuleStatus::Active)
            ->where('level', ModuleLevel::AdminOnly)
            ->with(['functionalities' => fn ($q) => $q->where('status', FunctionalityStatus::Active)->orderBy('slug')])
            ->orderBy('slug')
            ->get();

        $items = [];
        $color = '#0f766e';
        $icon = 'settings';

        foreach ($modules as $module) {
            $color = $module->color;
            $icon = $module->icon;

            foreach ($module->functionalities as $functionality) {
                $items[] = [
                    'slug' => $functionality->slug,
                    'label' => (string) $functionality->label,
                    'href' => $this->hrefFor($functionality),
                ];
            }
        }

        return [
            'slug' => 'configuracion',
            'label' => __('configuracion'),
            'color' => $color,
            'icon' => $icon,
            'functionalities' => $items,
        ];
    }

    private function isSuperAdmin(User $user): bool
    {
        $previous = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            return $user->hasRole('Super Admin');
        } finally {
            setPermissionsTeamId($previous);
        }
    }

    private function hrefFor(Functionality $functionality): string
    {
        return match ($functionality->slug) {
            'modules' => route('admin.modules.index', absolute: false),
            'functionalities' => route('admin.functionalities.index', absolute: false),
            'companies' => route('partners.companies.index', absolute: false),
            'company-modules' => route('admin.company-modules.index', absolute: false),
            'company-module-authorizations' => route('admin.company-module-authorizations.index', absolute: false),
            'roles' => route('admin.roles.index', absolute: false),
            'company-roles' => route('company.roles.index', absolute: false),
            default => '#',
        };
    }
}
