<?php

namespace App\Http\Middleware;

use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Partners\Models\Company;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\Request;
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
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'menu' => fn () => $user && $companyId
                ? app(MenuBuilder::class)->forUser($user)
                : [],
            'tenant' => [
                'id' => $company?->id,
                'name' => $company?->name,
                'logo_url' => $company?->logo,
            ],
            'available_companies' => fn () => $user
                ? $user->activeCompanies()->map(fn (Company $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->values()->all()
                : [],
        ];
    }
}
