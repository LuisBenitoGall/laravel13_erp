<?php

use App\Domains\Partners\Http\Controllers\CompanyController;
use App\Domains\Partners\Http\Controllers\CompanyMemberController;
use Illuminate\Support\Facades\Route;

// CRUD companies / table: Module `empresas`.
Route::middleware(['auth', 'company', 'module:empresas'])->prefix('partners')->name('partners.')->group(function () {
    Route::get('companies/table', [CompanyController::class, 'table'])->name('companies.table');
    Route::resource('companies', CompanyController::class)->except(['show']);
});

// Members = Functionality `company-users` bajo Module `usuarios`.
Route::middleware(['auth', 'company', 'module:usuarios'])->prefix('partners')->name('partners.')->group(function () {
    Route::get('companies/{company}/members', [CompanyMemberController::class, 'index'])
        ->name('companies.members.index');
    Route::put('companies/{company}/members/{user}/role', [CompanyMemberController::class, 'updateRole'])
        ->name('companies.members.role');
});
