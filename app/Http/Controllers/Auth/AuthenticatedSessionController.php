<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Support\Auth\PostLoginRedirect;
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

    public function store(LoginRequest $request, SetCompanySession $setCompanySession): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('credenciales_incorrectas'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $companies = $user->activeCompanies();

        if ($companies->isEmpty()) {
            $setCompanySession->clear($request);

            return redirect()->route('onboarding.company');
        }

        if ($companies->count() === 1) {
            $setCompanySession->handle($request, $user, (int) $companies->first()->id);

            return redirect()->intended(app(PostLoginRedirect::class)->route($user));
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
