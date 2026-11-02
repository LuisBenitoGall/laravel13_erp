<?php

use App\Domains\Admin\Http\Controllers\CompanyModuleAuthorizationController;
use App\Domains\Admin\Http\Controllers\CompanyModuleController;
use App\Domains\Admin\Http\Controllers\CompanyRoleController;
use App\Domains\Admin\Http\Controllers\FunctionalityController;
use App\Domains\Admin\Http\Controllers\ModuleController;
use App\Domains\Admin\Http\Controllers\PermissionController;
use App\Domains\Admin\Http\Controllers\PlatformRoleController;
use Illuminate\Support\Facades\Route;

// El catálogo de módulos queda fuera de `module:admin`: si `admin` pasa a Inactive,
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

    // Mutaciones usadas desde Module Edit (CRUD anidado); fuera de module:admin como el catálogo.
    Route::patch('functionalities/{functionality}/status', [FunctionalityController::class, 'updateStatus'])
        ->name('functionalities.status');
    Route::put('functionalities/{functionality}', [FunctionalityController::class, 'update'])
        ->name('functionalities.update');
    Route::delete('functionalities/{functionality}', [FunctionalityController::class, 'destroy'])
        ->name('functionalities.destroy');
});

Route::middleware(['auth', 'company', 'module:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('permissions/table', [PermissionController::class, 'table'])->name('permissions.table');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('roles/table', [PlatformRoleController::class, 'table'])->name('roles.table');
    Route::resource('roles', PlatformRoleController::class)->except(['show']);

    Route::get('company-modules', [CompanyModuleController::class, 'index'])->name('company-modules.index');
    Route::post('company-modules/{module}/enable', [CompanyModuleController::class, 'enable'])->name('company-modules.enable');
    Route::post('company-modules/{module}/disable', [CompanyModuleController::class, 'disable'])->name('company-modules.disable');

    Route::get('company-module-authorizations', [CompanyModuleAuthorizationController::class, 'index'])
        ->name('company-module-authorizations.index');
    Route::post('company-module-authorizations/{company}/{module}/authorize', [CompanyModuleAuthorizationController::class, 'authorizeModule'])
        ->name('company-module-authorizations.authorize');
    Route::post('company-module-authorizations/{company}/{module}/revoke', [CompanyModuleAuthorizationController::class, 'revoke'])
        ->name('company-module-authorizations.revoke');
});

Route::middleware(['auth', 'company', 'module:workspace'])->prefix('company')->name('company.')->group(function () {
    Route::get('roles/table', [CompanyRoleController::class, 'table'])->name('roles.table');
    Route::resource('roles', CompanyRoleController::class)
        ->except(['show'])
        ->parameters(['roles' => 'companyRole']);
});
