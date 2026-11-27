<?php

namespace App\Domains\Admin\Support;

/**
 * Single source slug Functionality → href relativo (MenuBuilder + PreferenceModuleResolver).
 * Primer segmento = Module slug inglés (kernel §4).
 */
final class MenuFunctionalityHref
{
    public static function for(string $slug): string
    {
        return match ($slug) {
            'modules' => route('admin.modules.index', absolute: false),
            'companies' => route('admin.companies.index', absolute: false),
            'delegations' => route('admin.delegations.index', absolute: false),
            'company-directory' => route('admin.company-directory.index', absolute: false),
            'workplaces' => route('admin.workplaces.index', absolute: false),
            'company-settings' => route('admin.company-settings.index', absolute: false),
            'company-modules' => route('admin.company-modules.index', absolute: false),
            'company-module-authorizations' => route('admin.company-module-authorizations.index', absolute: false),
            'roles' => route('admin.roles.index', absolute: false),
            'permissions' => route('admin.permissions.index', absolute: false),
            'countries' => route('admin.countries.index', absolute: false),
            'currencies' => route('admin.currencies.index', absolute: false),
            'exchange-rates' => route('admin.exchange-rates.index', absolute: false),
            'banks' => route('admin.banks.index', absolute: false),
            'units' => route('admin.units.index', absolute: false),
            'sectors' => route('admin.sectors.index', absolute: false),
            'payment-methods' => route('admin.payment-methods.index', absolute: false),
            'contents' => route('admin.contents.index', absolute: false),
            'gallery' => route('admin.gallery.index', absolute: false),
            'accounts' => route('admin.accounts.index', absolute: false),
            'vat-types' => route('admin.vat-types.index', absolute: false),
            'accounting-account-types' => route('admin.accounting-account-types.index', absolute: false),
            'company-roles' => route('admin.company-roles.index', absolute: false),
            'company-users' => route('admin.company-users.index', absolute: false),
            'all-users' => route('admin.all-users.index', absolute: false),
            'profile' => route('admin.account.show', absolute: false),
            'contract-types' => route('rrhh.contract-types.index', absolute: false),
            'employees' => route('rrhh.employees.index', absolute: false),
            'calendars' => route('rrhh.calendars.index', absolute: false),
            'day-types' => route('rrhh.day-types.index', absolute: false),
            'catalogue' => route('products.index', absolute: false),
            'products' => route('products.index', absolute: false),
            'families' => route('products.families.index', absolute: false),
            'categories' => route('products.categories.index', absolute: false),
            default => '#',
        };
    }
}
