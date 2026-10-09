<?php

use App\Domains\Partners\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'company', 'module:admin'])->prefix('partners')->name('partners.')->group(function () {
    Route::get('companies/table', [CompanyController::class, 'table'])->name('companies.table');
    Route::resource('companies', CompanyController::class)->except(['show']);
});
