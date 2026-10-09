<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\CompanySwitchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('companies/select', [CompanySelectionController::class, 'create'])->name('companies.select');
    Route::post('companies/select', [CompanySelectionController::class, 'store'])->name('companies.select.store');

    Route::post('companies/switch', [CompanySwitchController::class, 'store'])
        ->middleware('company')
        ->name('companies.switch');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.modules.index')
        : Inertia::render('Welcome');
})->name('home');
