<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\OnboardCompanyRequest;
use App\Support\Auth\PostLoginRedirect;
use App\Support\Strings\UniqueSlug;
use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OnboardCompanyController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        $user = request()->user();

        if ($user !== null && $user->activeCompanies()->isNotEmpty()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/OnboardCompany');
    }

    public function store(
        OnboardCompanyRequest $request,
        CreateCompany $createCompany,
        SetCompanySession $setCompanySession,
        PostLoginRedirect $postLoginRedirect,
    ): RedirectResponse {
        $validated = $request->validated();
        $name = $validated['name'] ?? $validated['tradename'];
        $tradename = $validated['tradename'] ?? $validated['name'];

        $slug = UniqueSlug::from(
            (string) ($tradename ?: $name),
            fn (string $candidate): bool => Company::query()->where('slug', $candidate)->exists(),
        );

        $company = $createCompany->handle([
            'name' => $name,
            'slug' => $slug,
            'tradename' => $tradename,
            'nif' => $validated['nif'],
            'status' => CompanyStatus::Active->value,
            'logo' => null,
        ], $request->user());

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
            $path = $file->storeAs(
                'companies/'.$company->id,
                'logo.'.$ext,
                'public',
            );
            $company->update(['logo' => $path]);
        }

        $setCompanySession->handle($request, $request->user(), (int) $company->id);

        return redirect()->intended($postLoginRedirect->route($request->user()));
    }
}
