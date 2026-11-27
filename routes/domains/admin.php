<?php

use App\Domains\Admin\Http\Controllers\AccountController;
use App\Domains\Admin\Http\Controllers\AccountingAccountTypeController;
use App\Domains\Admin\Http\Controllers\AllUserController;
use App\Domains\Admin\Http\Controllers\BankController;
use App\Domains\Admin\Http\Controllers\CompanyController;
use App\Domains\Admin\Http\Controllers\CompanyDirectoryController;
use App\Domains\Admin\Http\Controllers\CompanyMemberController;
use App\Domains\Admin\Http\Controllers\CompanyModuleAuthorizationController;
use App\Domains\Admin\Http\Controllers\CompanyModuleController;
use App\Domains\Admin\Http\Controllers\CompanyRoleController;
use App\Domains\Admin\Http\Controllers\CompanySettingsController;
use App\Domains\Admin\Http\Controllers\CompanyUserController;
use App\Domains\Admin\Http\Controllers\ContentController;
use App\Domains\Admin\Http\Controllers\CountryController;
use App\Domains\Admin\Http\Controllers\CurrencyController;
use App\Domains\Admin\Http\Controllers\DelegationController;
use App\Domains\Admin\Http\Controllers\ExchangeRateController;
use App\Domains\Admin\Http\Controllers\FunctionalityController;
use App\Domains\Admin\Http\Controllers\MediaAssetController;
use App\Domains\Admin\Http\Controllers\ModuleController;
use App\Domains\Admin\Http\Controllers\NoteController;
use App\Domains\Admin\Http\Controllers\PaymentMethodController;
use App\Domains\Admin\Http\Controllers\PermissionController;
use App\Domains\Admin\Http\Controllers\PlatformRoleController;
use App\Domains\Admin\Http\Controllers\PreferenceController;
use App\Domains\Admin\Http\Controllers\ProvinceController;
use App\Domains\Admin\Http\Controllers\SectorController;
use App\Domains\Admin\Http\Controllers\TableFilterPresetController;
use App\Domains\Admin\Http\Controllers\TownController;
use App\Domains\Admin\Http\Controllers\UnitController;
use App\Domains\Admin\Http\Controllers\UserCompanyController;
use App\Domains\Admin\Http\Controllers\UserDocController;
use App\Domains\Admin\Http\Controllers\UserEmailController;
use App\Domains\Admin\Http\Controllers\UserEmploymentPeriodController;
use App\Domains\Admin\Http\Controllers\UserImageController;
use App\Domains\Admin\Http\Controllers\UserPhoneController;
use App\Domains\Admin\Http\Controllers\VatTypeController;
use App\Domains\Admin\Http\Controllers\WorkplaceController;
use App\Http\Controllers\AccountController as CompanyAccountPortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// El catálogo de módulos queda fuera de `module:configuracion`: si pasa a Inactive,
// el listado y el toggle siguen accesibles para volver a activarlo. El resto del
// área admin sigue detrás del middleware.
// Catálogo Modules (SA): fuera de module:settings para poder reactivar si Inactive.
Route::middleware(['auth', 'company'])->prefix('settings')->name('admin.')->group(function () {
    Route::get('modules/table', [ModuleController::class, 'table'])->name('modules.table');
    Route::patch('modules/{module}/status', [ModuleController::class, 'updateStatus'])->name('modules.status');
    Route::get('modules/{module}/functionalities/table', [FunctionalityController::class, 'moduleTable'])
        ->name('modules.functionalities.table');
    Route::post('modules/{module}/functionalities', [FunctionalityController::class, 'moduleStore'])
        ->name('modules.functionalities.store');
    Route::resource('modules', ModuleController::class)->except(['show']);

    // Mutaciones usadas desde Module Edit (CRUD anidado); fuera de module:settings como el catálogo.
    Route::patch('functionalities/{functionality}/status', [FunctionalityController::class, 'updateStatus'])
        ->name('functionalities.status');
    Route::put('functionalities/{functionality}', [FunctionalityController::class, 'update'])
        ->name('functionalities.update');
    Route::delete('functionalities/{functionality}', [FunctionalityController::class, 'destroy'])
        ->name('functionalities.destroy');
});

Route::middleware(['auth', 'company'])->prefix('account')->name('admin.')->group(function () {
    // Favoritos personales (sin Functionality de menú; D4/D6).
    Route::post('preferences', [PreferenceController::class, 'store'])->name('preferences.store');
    Route::delete('preferences/{preference}', [PreferenceController::class, 'destroy'])
        ->name('preferences.destroy');

    // Presets de filtros avanzados ServerTable (personales; sin Functionality).
    Route::get('table-filter-presets', [TableFilterPresetController::class, 'index'])
        ->name('table-filter-presets.index');
    Route::post('table-filter-presets', [TableFilterPresetController::class, 'store'])
        ->name('table-filter-presets.store');
    Route::patch('table-filter-presets/{table_filter_preset}', [TableFilterPresetController::class, 'update'])
        ->name('table-filter-presets.update');
    Route::delete('table-filter-presets/{table_filter_preset}', [TableFilterPresetController::class, 'destroy'])
        ->name('table-filter-presets.destroy');

    // Notas internas personales (sin company_id; D2/D6).
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
});

Route::middleware(['auth', 'company', 'module:account'])->prefix('account')->name('admin.')->group(function () {
    Route::get('company-modules', [CompanyModuleController::class, 'index'])->name('company-modules.index');
    Route::post('company-modules/{module}/enable', [CompanyModuleController::class, 'enable'])->name('company-modules.enable');
    Route::post('company-modules/{module}/disable', [CompanyModuleController::class, 'disable'])->name('company-modules.disable');

    // Mi cuenta SaaS (histórico + renovación). ≠ catálogo Configuración → Cuentas.
    Route::get('account', [CompanyAccountPortalController::class, 'show'])->name('account.show');
    Route::get('account/company-accounts/table', [CompanyAccountPortalController::class, 'table'])
        ->name('account.company-accounts.table');
    Route::get('account-renew', [CompanyAccountPortalController::class, 'renewForm'])->name('account.renew');
    Route::post('account-renew', [CompanyAccountPortalController::class, 'renew'])->name('account.renew.store');
});

Route::middleware(['auth', 'company', 'module:settings'])->prefix('settings')->name('admin.')->group(function () {
    Route::get('permissions/table', [PermissionController::class, 'table'])->name('permissions.table');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('roles/table', [PlatformRoleController::class, 'table'])->name('roles.table');
    Route::patch('roles/{role}/permissions', [PlatformRoleController::class, 'togglePermission'])
        ->name('roles.permissions.toggle');
    Route::patch('roles/{role}/functionalities', [PlatformRoleController::class, 'toggleFunctionality'])
        ->name('roles.functionalities.toggle');
    Route::post('roles/{role}/copy', [PlatformRoleController::class, 'copy'])->name('roles.copy');
    Route::resource('roles', PlatformRoleController::class)->except(['show']);

    // Geografía global (Country → Province → Town); permisos `countries.*` (D3/D4/D5).
    Route::get('countries/table', [CountryController::class, 'table'])->name('countries.table');
    Route::patch('countries/{country}/status', [CountryController::class, 'updateStatus'])->name('countries.status');
    Route::resource('countries', CountryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('countries/{country}/provinces/table', [ProvinceController::class, 'table'])
        ->name('countries.provinces.table');
    Route::get('countries/{country}/provinces/options', [ProvinceController::class, 'options'])
        ->name('countries.provinces.options');
    Route::get('countries/{country}/provinces', [ProvinceController::class, 'index'])
        ->name('countries.provinces.index');
    Route::post('countries/{country}/provinces', [ProvinceController::class, 'store'])
        ->name('countries.provinces.store');
    Route::put('provinces/{province}', [ProvinceController::class, 'update'])->name('provinces.update');
    Route::patch('provinces/{province}/status', [ProvinceController::class, 'updateStatus'])->name('provinces.status');
    Route::delete('provinces/{province}', [ProvinceController::class, 'destroy'])->name('provinces.destroy');

    Route::get('provinces/{province}/towns/table', [TownController::class, 'table'])
        ->name('provinces.towns.table');
    Route::get('provinces/{province}/towns/options', [TownController::class, 'options'])
        ->name('provinces.towns.options');
    Route::get('provinces/{province}/towns', [TownController::class, 'index'])
        ->name('provinces.towns.index');
    Route::post('provinces/{province}/towns', [TownController::class, 'store'])
        ->name('provinces.towns.store');
    Route::put('towns/{town}', [TownController::class, 'update'])->name('towns.update');
    Route::patch('towns/{town}/status', [TownController::class, 'updateStatus'])->name('towns.status');
    Route::delete('towns/{town}', [TownController::class, 'destroy'])->name('towns.destroy');

    // Monedas globales (catálogo plano); permisos `currencies.*`.
    Route::get('currencies/table', [CurrencyController::class, 'table'])->name('currencies.table');
    Route::patch('currencies/{currency}/status', [CurrencyController::class, 'updateStatus'])->name('currencies.status');
    Route::resource('currencies', CurrencyController::class)->only(['index', 'store', 'update', 'destroy']);

    // Cotizaciones globales (catálogo par dirigido); permisos `exchange-rates.*`.
    Route::get('exchange-rates/table', [ExchangeRateController::class, 'table'])->name('exchange-rates.table');
    Route::resource('exchange-rates', ExchangeRateController::class)
        ->parameters(['exchange-rates' => 'exchange_rate'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Bancos globales (Admin); permisos `banks.*`.
    Route::get('banks/table', [BankController::class, 'table'])->name('banks.table');
    Route::patch('banks/{bank}/status', [BankController::class, 'updateStatus'])->name('banks.status');
    Route::resource('banks', BankController::class)->only(['index', 'store', 'update', 'destroy']);

    // Unidades globales (catálogo plano); permisos `units.*`.
    Route::get('units/table', [UnitController::class, 'table'])->name('units.table');
    Route::patch('units/{unit}/status', [UnitController::class, 'updateStatus'])->name('units.status');
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);

    // Cuentas SaaS globales (plantillas); permisos `accounts.*`. ≠ Module cuenta / AccountingAccount.
    Route::get('accounts/table', [AccountController::class, 'table'])->name('accounts.table');
    Route::get('accounts/renewal', [AccountController::class, 'renewal'])->name('accounts.renewal');
    Route::patch('accounts/{account}/status', [AccountController::class, 'updateStatus'])->name('accounts.status');
    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);

    // Sectores globales (catálogo plano traducibles); permisos `sectors.*`.
    Route::get('sectors/table', [SectorController::class, 'table'])->name('sectors.table');
    Route::patch('sectors/{sector}/status', [SectorController::class, 'updateStatus'])->name('sectors.status');
    Route::resource('sectors', SectorController::class)->only(['index', 'store', 'update', 'destroy']);

    // Métodos de pago globales (catálogo plano traducibles); permisos `payment-methods.*`.
    Route::get('payment-methods/table', [PaymentMethodController::class, 'table'])->name('payment-methods.table');
    Route::patch('payment-methods/{payment_method}/status', [PaymentMethodController::class, 'updateStatus'])
        ->name('payment-methods.status');
    Route::resource('payment-methods', PaymentMethodController::class)
        ->parameters(['payment-methods' => 'payment_method'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Contenidos (Help/Article); permisos `contents.*`. Create mínimo; Edit real.
    Route::get('contents/table', [ContentController::class, 'table'])->name('contents.table');
    Route::patch('contents/{content}/status', [ContentController::class, 'updateStatus'])
        ->name('contents.status');
    Route::resource('contents', ContentController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Galería (MediaAsset); permisos `gallery.*`. Grid; altas/copias company_id null.
    Route::get('gallery/picker', [MediaAssetController::class, 'picker'])
        ->name('gallery.picker');
    Route::get('gallery/{media_asset}/file', [MediaAssetController::class, 'file'])
        ->name('gallery.file');
    Route::post('gallery/{media_asset}/copies', [MediaAssetController::class, 'storeCopy'])
        ->name('gallery.copies');
    Route::resource('gallery', MediaAssetController::class)
        ->parameters(['gallery' => 'media_asset'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Tipos de IVA globales por país; permisos `vat-types.*`.
    Route::get('vat-types/table', [VatTypeController::class, 'table'])->name('vat-types.table');
    Route::patch('vat-types/{vatType}/status', [VatTypeController::class, 'updateStatus'])->name('vat-types.status');
    Route::resource('vat-types', VatTypeController::class)
        ->parameters(['vat-types' => 'vatType'])
        ->only(['index', 'store', 'update', 'destroy']);

    // Grupos contables PGC; permisos `accounting-account-types.*`.
    Route::get('accounting-account-types/table', [AccountingAccountTypeController::class, 'table'])
        ->name('accounting-account-types.table');
    Route::get('accounting-account-types/show', [AccountingAccountTypeController::class, 'show'])
        ->name('accounting-account-types.show');
    Route::resource('accounting-account-types', AccountingAccountTypeController::class)
        ->parameters(['accounting-account-types' => 'accountingAccountType'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('company-module-authorizations', [CompanyModuleAuthorizationController::class, 'index'])
        ->name('company-module-authorizations.index');
    Route::post('company-module-authorizations/{company}/{module}/authorize', [CompanyModuleAuthorizationController::class, 'authorizeModule'])
        ->name('company-module-authorizations.authorize');
    Route::post('company-module-authorizations/{company}/{module}/revoke', [CompanyModuleAuthorizationController::class, 'revoke'])
        ->name('company-module-authorizations.revoke');
});

Route::middleware(['auth', 'company', 'module:companies'])->prefix('companies')->name('admin.')->group(function () {
    Route::get('company-directory', [CompanyDirectoryController::class, 'index'])
        ->name('company-directory.index');
    Route::get('company-directory/{company}', [CompanyDirectoryController::class, 'show'])
        ->name('company-directory.show');

    Route::get('company-settings', [CompanySettingsController::class, 'index'])
        ->name('company-settings.index');
    Route::put('company-settings/currencies', [CompanySettingsController::class, 'syncCurrencies'])
        ->name('company-settings.currencies.sync');
    Route::post('company-settings/dismiss-modal', [CompanySettingsController::class, 'dismissModal'])
        ->name('company-settings.dismiss-modal');

    Route::get('workplaces/table', [WorkplaceController::class, 'table'])->name('workplaces.table');
    Route::get('workplaces/geo/countries', [WorkplaceController::class, 'countryOptions'])
        ->name('workplaces.geo.countries');
    Route::get('workplaces/geo/countries/{country}/provinces', [WorkplaceController::class, 'provinceOptions'])
        ->name('workplaces.geo.provinces');
    Route::get('workplaces/geo/provinces/{province}/towns', [WorkplaceController::class, 'townOptions'])
        ->name('workplaces.geo.towns');
    Route::patch('workplaces/{workplace}/status', [WorkplaceController::class, 'updateStatus'])
        ->name('workplaces.status');
    Route::put('workplaces/{workplace}/delegations', [WorkplaceController::class, 'syncDelegations'])
        ->whereNumber('workplace')
        ->name('workplaces.delegations.sync');
    Route::resource('workplaces', WorkplaceController::class)->except(['show']);

    // Delegaciones = Companies hijas de la matriz de sesión (≠ Mis empresas; sin company_accounts).
    Route::get('delegations/table', [DelegationController::class, 'table'])->name('delegations.table');
    Route::patch('delegations/{delegation}/status', [DelegationController::class, 'updateStatus'])
        ->name('delegations.status');
    Route::put('delegations/{delegation}/currencies', [DelegationController::class, 'syncCurrencies'])
        ->name('delegations.currencies.sync');
    Route::get('delegations/{delegation}/workplaces/table', [DelegationController::class, 'workplacesTable'])
        ->name('delegations.workplaces.table');
    Route::put('delegations/{delegation}/preferred-workplace', [DelegationController::class, 'setPreferredWorkplace'])
        ->name('delegations.preferred-workplace');
    Route::resource('delegations', DelegationController::class)->except(['show']);

    // Mis empresas en la raíz del Module `companies` (evita /companies/companies).
    Route::get('table', [CompanyController::class, 'table'])->name('companies.table');
    Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/', [CompanyController::class, 'store'])->name('companies.store');
    Route::patch('{company}/status', [CompanyController::class, 'updateStatus'])
        ->whereNumber('company')
        ->name('companies.status');
    Route::post('{company}/logo', [CompanyController::class, 'uploadLogo'])
        ->whereNumber('company')
        ->name('companies.logo');
    Route::put('{company}/parent', [CompanyController::class, 'updateParent'])
        ->whereNumber('company')
        ->name('companies.parent');
    Route::post('{company}/children', [CompanyController::class, 'attachChild'])
        ->whereNumber('company')
        ->name('companies.children.attach');
    Route::delete('{company}/children/{child}', [CompanyController::class, 'detachChild'])
        ->whereNumber('company')
        ->name('companies.children.detach');
    Route::put('{company}/sectors', [CompanyController::class, 'syncSectors'])
        ->whereNumber('company')
        ->name('companies.sectors.sync');
    Route::put('{company}/currencies', [CompanyController::class, 'syncCurrencies'])
        ->whereNumber('company')
        ->name('companies.currencies.sync');
    Route::get('{company}/edit', [CompanyController::class, 'edit'])
        ->whereNumber('company')
        ->name('companies.edit');
    Route::put('{company}', [CompanyController::class, 'update'])
        ->whereNumber('company')
        ->name('companies.update');
    Route::delete('{company}', [CompanyController::class, 'destroy'])
        ->whereNumber('company')
        ->name('companies.destroy');
});

Route::middleware(['auth', 'company', 'module:users'])->prefix('users')->name('admin.')->group(function () {
    Route::get('companies/{company}/members', [CompanyMemberController::class, 'index'])
        ->name('companies.members.index');
    Route::put('companies/{company}/members/{user}/role', [CompanyMemberController::class, 'updateRole'])
        ->name('companies.members.role');

    Route::get('company-roles/table', [CompanyRoleController::class, 'table'])->name('company-roles.table');
    Route::patch('company-roles/{companyRole}/permissions', [CompanyRoleController::class, 'togglePermission'])
        ->name('company-roles.permissions.toggle');
    Route::patch('company-roles/{companyRole}/functionalities', [CompanyRoleController::class, 'toggleFunctionality'])
        ->name('company-roles.functionalities.toggle');
    Route::resource('company-roles', CompanyRoleController::class)
        ->except(['show'])
        ->parameters(['company-roles' => 'companyRole']);
});

Route::middleware(['auth', 'company'])->prefix('account')->name('admin.')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('profile/emails', [UserEmailController::class, 'storeForProfile'])
        ->name('profile.emails.store');
    Route::delete('profile/emails/{userEmail}', [UserEmailController::class, 'destroyForProfile'])
        ->name('profile.emails.destroy');
    Route::post('profile/phones', [UserPhoneController::class, 'storeForProfile'])
        ->name('profile.phones.store');
    Route::put('profile/phones/{phone}', [UserPhoneController::class, 'updateForProfile'])
        ->name('profile.phones.update');
    Route::delete('profile/phones/{phone}', [UserPhoneController::class, 'destroyForProfile'])
        ->name('profile.phones.destroy');
    Route::post('profile/phones/{phone}/mark-primary', [UserPhoneController::class, 'markPrimaryForProfile'])
        ->name('profile.phones.mark-primary');
    Route::post('profile/images', [UserImageController::class, 'storeForProfile'])
        ->name('profile.images.store');
    Route::get('profile/images/{userImage}/file', [UserImageController::class, 'fileForProfile'])
        ->name('profile.images.file');
    Route::delete('profile/images/{userImage}', [UserImageController::class, 'destroyForProfile'])
        ->name('profile.images.destroy');
    Route::post('profile/images/{userImage}/mark-featured', [UserImageController::class, 'markFeaturedForProfile'])
        ->name('profile.images.mark-featured');
    Route::patch('profile/images/{userImage}/public', [UserImageController::class, 'updatePublicForProfile'])
        ->name('profile.images.public');
    Route::post('profile/docs', [UserDocController::class, 'storeForProfile'])
        ->name('profile.docs.store');
    Route::get('profile/docs/{userDoc}/download', [UserDocController::class, 'downloadForProfile'])
        ->name('profile.docs.download');
    Route::delete('profile/docs/{userDoc}', [UserDocController::class, 'destroyForProfile'])
        ->name('profile.docs.destroy');
    Route::get('profile/companies/table', [UserCompanyController::class, 'tableForProfile'])
        ->name('profile.companies.table');
    Route::put('profile/companies/{userCompany}', [UserCompanyController::class, 'updateForProfile'])
        ->name('profile.companies.update');
    Route::delete('profile/companies/{userCompany}', [UserCompanyController::class, 'destroyForProfile'])
        ->name('profile.companies.destroy');
    Route::get('profile/employment-periods/table', [UserEmploymentPeriodController::class, 'tableForProfile'])
        ->name('profile.employment-periods.table');
});

Route::middleware(['auth', 'company', 'module:users'])->prefix('users')->name('admin.')->group(function () {
    Route::get('all-users/table', [AllUserController::class, 'table'])->name('all-users.table');
    Route::get('all-users/{user}/quick', [AllUserController::class, 'quick'])->name('all-users.quick');
    Route::get('all-users/{user}/images/{userImage}/file', [UserImageController::class, 'fileForAllUser'])
        ->name('all-users.images.file');
    Route::get('all-users', [AllUserController::class, 'index'])->name('all-users.index');

    Route::get('company-users/table', [CompanyUserController::class, 'table'])->name('company-users.table');
    Route::get('company-users/create', [CompanyUserController::class, 'create'])->name('company-users.create');
    Route::post('company-users/bulk-detach', [CompanyUserController::class, 'bulkDetach'])
        ->name('company-users.bulk-detach');
    Route::post('company-users/bulk-status', [CompanyUserController::class, 'bulkStatus'])
        ->name('company-users.bulk-status');
    Route::post('company-users', [CompanyUserController::class, 'store'])->name('company-users.store');
    Route::get('company-users/{user}/quick', [CompanyUserController::class, 'quick'])->name('company-users.quick');
    Route::get('company-users/{user}/edit', [CompanyUserController::class, 'edit'])->name('company-users.edit');
    Route::put('company-users/{user}', [CompanyUserController::class, 'update'])->name('company-users.update');
    Route::put('company-users/{user}/password', [CompanyUserController::class, 'updatePassword'])
        ->name('company-users.password');
    Route::post('company-users/{user}/emails', [UserEmailController::class, 'storeForCompanyUser'])
        ->name('company-users.emails.store');
    Route::delete('company-users/{user}/emails/{userEmail}', [UserEmailController::class, 'destroyForCompanyUser'])
        ->name('company-users.emails.destroy');
    Route::post('company-users/{user}/phones', [UserPhoneController::class, 'storeForCompanyUser'])
        ->name('company-users.phones.store');
    Route::put('company-users/{user}/phones/{phone}', [UserPhoneController::class, 'updateForCompanyUser'])
        ->name('company-users.phones.update');
    Route::delete('company-users/{user}/phones/{phone}', [UserPhoneController::class, 'destroyForCompanyUser'])
        ->name('company-users.phones.destroy');
    Route::post('company-users/{user}/phones/{phone}/mark-primary', [UserPhoneController::class, 'markPrimaryForCompanyUser'])
        ->name('company-users.phones.mark-primary');
    Route::post('company-users/{user}/images', [UserImageController::class, 'storeForCompanyUser'])
        ->name('company-users.images.store');
    Route::get('company-users/{user}/images/{userImage}/file', [UserImageController::class, 'fileForCompanyUser'])
        ->name('company-users.images.file');
    Route::delete('company-users/{user}/images/{userImage}', [UserImageController::class, 'destroyForCompanyUser'])
        ->name('company-users.images.destroy');
    Route::post('company-users/{user}/images/{userImage}/mark-featured', [UserImageController::class, 'markFeaturedForCompanyUser'])
        ->name('company-users.images.mark-featured');
    Route::patch('company-users/{user}/images/{userImage}/public', [UserImageController::class, 'updatePublicForCompanyUser'])
        ->name('company-users.images.public');
    Route::post('company-users/{user}/docs', [UserDocController::class, 'storeForCompanyUser'])
        ->name('company-users.docs.store');
    Route::get('company-users/{user}/docs/{userDoc}/download', [UserDocController::class, 'downloadForCompanyUser'])
        ->name('company-users.docs.download');
    Route::delete('company-users/{user}/docs/{userDoc}', [UserDocController::class, 'destroyForCompanyUser'])
        ->name('company-users.docs.destroy');
    Route::get('company-users/{user}/companies/table', [UserCompanyController::class, 'tableForCompanyUser'])
        ->name('company-users.companies.table');
    Route::put('company-users/{user}/companies/{userCompany}', [UserCompanyController::class, 'updateForCompanyUser'])
        ->name('company-users.companies.update');
    Route::delete('company-users/{user}/companies/{userCompany}', [UserCompanyController::class, 'destroyForCompanyUser'])
        ->name('company-users.companies.destroy');
    Route::get('company-users/{user}/employment-periods/table', [UserEmploymentPeriodController::class, 'tableForCompanyUser'])
        ->name('company-users.employment-periods.table');
    Route::patch('company-users/{user}/status', [CompanyUserController::class, 'updateStatus'])
        ->name('company-users.status');
    Route::get('company-users', [CompanyUserController::class, 'index'])->name('company-users.index');
    Route::delete('company-users/{user}', [CompanyUserController::class, 'destroy'])
        ->name('company-users.destroy');
});
