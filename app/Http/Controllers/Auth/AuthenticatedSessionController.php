<?php

namespace App\Http\Controllers\Auth;

use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request, SetCompanySession $setCompanySession): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $companies = $user->activeCompanies();

        if ($companies->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('auth.no_company'),
            ])->onlyInput('email');
        }

        if ($companies->count() === 1) {
            $setCompanySession->handle($request, $user, (int) $companies->first()->id);

            return redirect()->intended(route('admin.modules.index'));
        }

        $setCompanySession->clear($request);

        return redirect()->route('companies.select');
    }

    public function destroy(Request $request, SetCompanySession $setCompanySession): RedirectResponse
    {
        $setCompanySession->clear($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
