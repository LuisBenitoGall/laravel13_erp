<?php

use App\Domains\Admin\Http\Controllers\CompanyModuleAuthorizationController;
use App\Domains\Admin\Http\Controllers\CompanyModuleController;
use App\Domains\Admin\Http\Controllers\CompanyRoleController;
use App\Domains\Admin\Http\Controllers\FunctionalityController;
use App\Domains\Admin\Http\Controllers\ModuleController;
use App\Domains\Admin\Http\Controllers\PlatformRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'company', 'module:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('modules/table', [ModuleController::class, 'table'])->name('modules.table');
    Route::patch('modules/{module}/status', [ModuleController::class, 'updateStatus'])->name('modules.status');
    Route::resource('modules', ModuleController::class)->except(['show']);

    Route::get('functionalities/table', [FunctionalityController::class, 'table'])->name('functionalities.table');
    Route::resource('functionalities', FunctionalityController::class)->except(['show']);

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
