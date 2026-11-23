<?php

namespace App\Http\Controllers;

use App\Support\Auth\PostLoginRedirect;
use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySelectionController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if ($user->activeCompanies()->isEmpty()) {
            return redirect()->route('onboarding.company');
        }

        // Solo matrices; si el user solo tiene Delegaciones se muestra la lista vacía
        // (sin bypass hacia una hija).
        $companies = $user->selectableCompanies();

        if ($companies->count() === 1) {
            app(SetCompanySession::class)->handle($request, $user, (int) $companies->first()->id);

            return redirect()->intended(app(PostLoginRedirect::class)->route($user));
        }

        return Inertia::render('Auth/SelectCompany', [
            'companies' => $companies->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'tradename' => $c->tradename,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request, SetCompanySession $setCompanySession): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        try {
            $setCompanySession->handle($request, $request->user(), (int) $data['company_id']);
        } catch (\RuntimeException) {
            return back()->withErrors([
                'company_id' => __('empresa_acceso_prohibido'),
            ]);
        }

        return redirect()->intended(app(PostLoginRedirect::class)->route($request->user()));
    }
}
