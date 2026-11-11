<?php

use App\Domains\Admin\Http\Controllers\UserCompanyController;
use App\Domains\Admin\Http\Controllers\UserDocController;
use App\Domains\Admin\Http\Controllers\UserEmailController;
use App\Domains\Admin\Http\Controllers\UserImageController;
use App\Domains\Admin\Http\Controllers\UserPhoneController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OnboardCompanyController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
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

    Route::get('account', [AccountController::class, 'show'])
        ->middleware('company')
        ->name('account.show');

    Route::middleware('company')->group(function () {
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
    });
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('Welcome');
})->name('home');
