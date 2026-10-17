<?php

use App\Domains\Partners\Http\Controllers\CompanyController;
use App\Domains\Partners\Http\Controllers\CompanyMemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'company', 'module:admin'])->prefix('partners')->name('partners.')->group(function () {
    Route::get('companies/table', [CompanyController::class, 'table'])->name('companies.table');
    Route::resource('companies', CompanyController::class)->except(['show']);

    Route::get('companies/{company}/members', [CompanyMemberController::class, 'index'])
        ->name('companies.members.index');
    Route::put('companies/{company}/members/{user}/role', [CompanyMemberController::class, 'updateRole'])
        ->name('companies.members.role');
});
