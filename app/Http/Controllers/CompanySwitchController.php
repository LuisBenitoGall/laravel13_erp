<?php

namespace App\Http\Controllers;

use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
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

        return back()->with('success', __('empresa_cambiada'));
    }
}
