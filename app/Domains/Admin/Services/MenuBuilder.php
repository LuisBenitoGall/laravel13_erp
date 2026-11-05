<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;

/**
 * Menú 100 % data-driven: raíz = Module; hijo = Functionality (o Module hoja).
 * Permisos AND: comodín + viewAny (hoja: solo comodín). Cache de catálogo Active.
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
     *     href?: string,
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
        $isSa = $this->isSuperAdmin($user);

        $menu = [];

        foreach (MenuCatalogCache::modules() as $module) {
            $isAdminOnly = $module['level'] === ModuleLevel::AdminOnly->value;
            $layersOk = $eligibleIds->contains($module['id'])
                || ($isSa && $isAdminOnly);

            if (! $layersOk) {
                continue;
            }

            $entry = $this->moduleEntry($user, $module, $isSa);

            if ($entry !== null) {
                $menu[] = $entry;
            }
        }

        return $menu;
    }

    /**
     * @param  array{
     *     id: int,
     *     slug: string,
     *     label: array<string, string>,
     *     color: string,
     *     icon: string,
     *     level: string,
     *     functionalities: list<array{id: int, slug: string, label: array<string, string>}>
     * }  $module
     * @return array{
     *     slug: string,
     *     label: string,
     *     color: string,
     *     icon: string,
     *     href?: string,
     *     functionalities: list<array{slug: string, label: string, href: string}>
     * }|null
     */
    private function moduleEntry(User $user, array $module, bool $isSa): ?array
    {
        $hasWildcard = $isSa || $user->can($module['slug'].'.access');
        $functionalities = $module['functionalities'];

        if ($functionalities === []) {
            if (! $hasWildcard) {
                return null;
            }

            return [
                'slug' => $module['slug'],
                'label' => $this->resolveLabel($module['label']),
                'color' => $module['color'],
                'icon' => $module['icon'],
                'href' => $this->moduleHref($module['slug']),
                'functionalities' => [],
            ];
        }

        if (! $hasWildcard) {
            return null;
        }

        $items = [];

        foreach ($functionalities as $functionality) {
            if ($isSa || $user->can($functionality['slug'].'.viewAny')) {
                $items[] = $this->functionalityItem($functionality);
            }
        }

        if ($items === []) {
            return null;
        }

        return [
            'slug' => $module['slug'],
            'label' => $this->resolveLabel($module['label']),
            'color' => $module['color'],
            'icon' => $module['icon'],
            'functionalities' => $items,
        ];
    }

    /**
     * @param  array{id: int, slug: string, label: array<string, string>}  $functionality
     * @return array{slug: string, label: string, href: string}
     */
    private function functionalityItem(array $functionality): array
    {
        return [
            'slug' => $functionality['slug'],
            'label' => $this->labelFor($functionality),
            'href' => $this->hrefFor($functionality['slug']),
        ];
    }

    /**
     * @param  array{slug: string, label: array<string, string>}  $functionality
     */
    private function labelFor(array $functionality): string
    {
        return match ($functionality['slug']) {
            'companies' => __('mis_empresas'),
            'company-users' => __('usuarios_empresa'),
            'all-users' => __('todos_los_usuarios'),
            'company-modules' => __('mis_modulos'),
            'profile' => __('mi_cuenta'),
            'permissions' => __('permisos'),
            default => $this->resolveLabel($functionality['label']),
        };
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function resolveLabel(array $translations): string
    {
        $locale = app()->getLocale();

        if (isset($translations[$locale]) && $translations[$locale] !== '') {
            return $translations[$locale];
        }

        if (isset($translations['es']) && $translations['es'] !== '') {
            return $translations['es'];
        }

        $first = reset($translations);

        return is_string($first) ? $first : '';
    }

    private function moduleHref(string $slug): string
    {
        return match ($slug) {
            'dashboard' => route('dashboard', absolute: false),
            default => '#',
        };
    }

    private function isSuperAdmin(User $user): bool
    {
        $previous = getPermissionsTeamId();
        setPermissionsTeamId(null);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        try {
            return $user->hasRole('Super Admin');
        } finally {
            setPermissionsTeamId($previous);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function hrefFor(string $slug): string
    {
        return match ($slug) {
            'modules' => route('admin.modules.index', absolute: false),
            'companies' => route('partners.companies.index', absolute: false),
            'company-modules' => route('admin.company-modules.index', absolute: false),
            'company-module-authorizations' => route('admin.company-module-authorizations.index', absolute: false),
            'roles' => route('admin.roles.index', absolute: false),
            'permissions' => route('admin.permissions.index', absolute: false),
            'countries' => route('admin.countries.index', absolute: false),
            'currencies' => route('admin.currencies.index', absolute: false),
            'banks' => route('admin.banks.index', absolute: false),
            'units' => route('admin.units.index', absolute: false),
            'vat-types' => route('admin.vat-types.index', absolute: false),
            'accounting-account-types' => route('admin.accounting-account-types.index', absolute: false),
            'company-roles' => route('company.roles.index', absolute: false),
            'company-users' => route('admin.company-users.index', absolute: false),
            'all-users' => route('admin.all-users.index', absolute: false),
            'profile' => route('profile.show', absolute: false),
            default => '#',
        };
    }
}
