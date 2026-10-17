<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OnboardCompanyController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::get('legal/notice', [LegalController::class, 'notice'])->name('legal.notice');
Route::get('legal/terms', [LegalController::class, 'terms'])->name('legal.terms');

Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('onboarding/company', [OnboardCompanyController::class, 'create'])->name('onboarding.company');
    Route::post('onboarding/company', [OnboardCompanyController::class, 'store'])->name('onboarding.company.store');

    Route::get('companies/select', [CompanySelectionController::class, 'create'])->name('companies.select');
    Route::post('companies/select', [CompanySelectionController::class, 'store'])->name('companies.select.store');

    Route::post('companies/switch', [CompanySwitchController::class, 'store'])
        ->middleware('company')
        ->name('companies.switch');

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware('company')->name('dashboard');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('Welcome');
})->name('home');
