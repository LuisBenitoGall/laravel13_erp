<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Account/ComingSoon');
    }
}
