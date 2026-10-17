<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Support\Auth\RegisterUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterUserRequest $request, RegisterUser $action): RedirectResponse
    {
        $user = $action->handle($request->safe()->only(['name', 'surname', 'email', 'password']));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.company');
    }
}
