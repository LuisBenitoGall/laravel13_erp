<?php

use App\Domains\Admin\Http\Controllers\FunctionalityController;
use App\Domains\Admin\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'module:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('modules/table', [ModuleController::class, 'table'])->name('modules.table');
    Route::resource('modules', ModuleController::class)->except(['show']);

    Route::get('functionalities/table', [FunctionalityController::class, 'table'])->name('functionalities.table');
    Route::resource('functionalities', FunctionalityController::class)->except(['show']);
});
