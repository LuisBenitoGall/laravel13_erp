<?php

use App\Domains\Admin\Http\Controllers\AccountingAccountTypeController;
use App\Domains\Admin\Http\Controllers\AllUserController;
use App\Domains\Admin\Http\Controllers\CompanyModuleAuthorizationController;
use App\Domains\Admin\Http\Controllers\CompanyModuleController;
use App\Domains\Admin\Http\Controllers\CompanyRoleController;
use App\Domains\Admin\Http\Controllers\CompanyUserController;
use App\Domains\Admin\Http\Controllers\CountryController;
use App\Domains\Admin\Http\Controllers\CurrencyController;
use App\Domains\Admin\Http\Controllers\FunctionalityController;
use App\Domains\Admin\Http\Controllers\ModuleController;
use App\Domains\Admin\Http\Controllers\PermissionController;
use App\Domains\Admin\Http\Controllers\PlatformRoleController;
use App\Domains\Admin\Http\Controllers\ProvinceController;
use App\Domains\Admin\Http\Controllers\TownController;
use App\Domains\Admin\Http\Controllers\UnitController;
use App\Domains\Admin\Http\Controllers\VatTypeController;
use App\Domains\Banks\Http\Controllers\BankController;
use Illuminate\Support\Facades\Route;

// El catálogo de módulos queda fuera de `module:configuracion`: si pasa a Inactive,
// el listado y el toggle siguen accesibles para volver a activarlo. El resto del
// área admin sigue detrás del middleware.
Route::middleware(['auth', 'company'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('modules/table', [ModuleController::class, 'table'])->name('modules.table');
    Route::patch('modules/{module}/status', [ModuleController::class, 'updateStatus'])->name('modules.status');
    Route::get('modules/{module}/functionalities/table', [FunctionalityController::class, 'moduleTable'])
        ->name('modules.functionalities.table');
    Route::post('modules/{module}/functionalities', [FunctionalityController::class, 'moduleStore'])
        ->name('modules.functionalities.store');
    Route::resource('modules', ModuleController::class)->except(['show']);

    // Mutaciones usadas desde Module Edit (CRUD anidado); fuera de module:configuracion como el catálogo.
    Route::patch('functionalities/{functionality}/status', [FunctionalityController::class, 'updateStatus'])
        ->name('functionalities.status');
    Route::put('functionalities/{functionality}', [FunctionalityController::class, 'update'])
        ->name('functionalities.update');
    Route::delete('functionalities/{functionality}', [FunctionalityController::class, 'destroy'])
        ->name('functionalities.destroy');
});

Route::middleware(['auth', 'company', 'module:cuenta'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('company-modules', [CompanyModuleController::class, 'index'])->name('company-modules.index');
    Route::post('company-modules/{module}/enable', [CompanyModuleController::class, 'enable'])->name('company-modules.enable');
    Route::post('company-modules/{module}/disable', [CompanyModuleController::class, 'disable'])->name('company-modules.disable');
});

Route::middleware(['auth', 'company', 'module:configuracion'])->prefix('admin')->name('admin.')->group(function () {
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

    // Bancos globales (dominio Banks); permisos `banks.*`.
    Route::get('banks/table', [BankController::class, 'table'])->name('banks.table');
    Route::patch('banks/{bank}/status', [BankController::class, 'updateStatus'])->name('banks.status');
    Route::resource('banks', BankController::class)->only(['index', 'store', 'update', 'destroy']);

    // Unidades globales (catálogo plano); permisos `units.*`.
    Route::get('units/table', [UnitController::class, 'table'])->name('units.table');
    Route::patch('units/{unit}/status', [UnitController::class, 'updateStatus'])->name('units.status');
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);

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

Route::middleware(['auth', 'company', 'module:usuarios'])->prefix('company')->name('company.')->group(function () {
    Route::get('roles/table', [CompanyRoleController::class, 'table'])->name('roles.table');
    Route::patch('roles/{companyRole}/permissions', [CompanyRoleController::class, 'togglePermission'])
        ->name('roles.permissions.toggle');
    Route::patch('roles/{companyRole}/functionalities', [CompanyRoleController::class, 'toggleFunctionality'])
        ->name('roles.functionalities.toggle');
    Route::resource('roles', CompanyRoleController::class)
        ->except(['show'])
        ->parameters(['roles' => 'companyRole']);
});

Route::middleware(['auth', 'company', 'module:usuarios'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('all-users/table', [AllUserController::class, 'table'])->name('all-users.table');
    Route::get('all-users', [AllUserController::class, 'index'])->name('all-users.index');

    Route::get('company-users/table', [CompanyUserController::class, 'table'])->name('company-users.table');
    Route::get('company-users/create', [CompanyUserController::class, 'create'])->name('company-users.create');
    Route::post('company-users', [CompanyUserController::class, 'store'])->name('company-users.store');
    Route::get('company-users/{user}/edit', [CompanyUserController::class, 'edit'])->name('company-users.edit');
    Route::put('company-users/{user}', [CompanyUserController::class, 'update'])->name('company-users.update');
    Route::put('company-users/{user}/password', [CompanyUserController::class, 'updatePassword'])
        ->name('company-users.password');
    Route::patch('company-users/{user}/status', [CompanyUserController::class, 'updateStatus'])
        ->name('company-users.status');
    Route::get('company-users', [CompanyUserController::class, 'index'])->name('company-users.index');
    Route::delete('company-users/{user}', [CompanyUserController::class, 'destroy'])
        ->name('company-users.destroy');
});
