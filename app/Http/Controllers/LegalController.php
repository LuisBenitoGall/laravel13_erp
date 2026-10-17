<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function notice(): Response
    {
        return Inertia::render('Legal/Notice');
    }

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms');
    }
}
