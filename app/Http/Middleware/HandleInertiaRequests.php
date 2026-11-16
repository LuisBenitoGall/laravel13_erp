<?php

namespace App\Http\Middleware;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Note;
use App\Domains\Admin\Services\CompanySetupReadiness;
use App\Domains\Admin\Services\HelpContentMap;
use App\Domains\Admin\Services\MenuBuilder;
use App\Support\I18n\LocaleResolver;
use App\Support\I18n\TranslationDictionary;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $user = $request->user();
        $companyId = app(CurrentCompany::class)->id();
        $company = $companyId !== null
            ? Company::query()->find($companyId)
            : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->loadMissing('featuredImage')->featuredImage?->url(),
                ] : null,
            ],
            'notes_count' => fn () => $user
                ? min(Note::query()->where('user_id', $user->id)->count(), Note::MAX_PER_USER)
                : 0,
            'help_tips' => fn () => $user
                ? app(HelpContentMap::class)->forLocale(App::getLocale())
                : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'menu' => fn () => $user && $companyId
                ? app(MenuBuilder::class)->forUser($user)
                : [],
            'branding' => [
                'logo_url' => (string) config('branding.logo_url'),
            ],
            'tenant' => [
                'id' => $company?->id,
                'name' => $company?->name,
                'logo_url' => $company?->logo
                    ? Storage::disk('public')->url($company->logo)
                    : null,
            ],
            'available_companies' => fn () => $user
                ? $user->activeCompanies()->map(fn (Company $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->values()->all()
                : [],
            'company_setup' => function () use ($request, $company) {
                if ($company === null) {
                    return null;
                }

                $evaluation = app(CompanySetupReadiness::class)->evaluate($company);
                $hubUrl = route('admin.company-settings.index', absolute: false);

                return [
                    'incomplete' => ! $evaluation['complete'],
                    'show_modal' => ! $evaluation['complete']
                        && $request->session()->get('company_setup.show_modal') === true,
                    'hub_url' => $hubUrl,
                    'wizard_url' => $hubUrl.'?wizard=1',
                    'completed_steps' => $evaluation['completed_steps'],
                    'total_steps' => $evaluation['total_steps'],
                    'steps' => $evaluation['steps'],
                ];
            },
            'locale' => App::getLocale(),
            'supportedLocales' => app(LocaleResolver::class)->supported(),
            'defaultLocale' => app(LocaleResolver::class)->default(),
            'translations' => app(TranslationDictionary::class)->forLocale(App::getLocale()),
        ];
    }
}
