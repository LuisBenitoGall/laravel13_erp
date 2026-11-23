<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateWorkplace;
use App\Domains\Admin\Actions\DeleteWorkplace;
use App\Domains\Admin\Actions\UpdateWorkplace;
use App\Domains\Admin\Http\Requests\DelegationWorkplaceRequest;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\DelegationPerimeter;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Sedes de una Delegación desde su Edit: company_id explícito de la hija,
 * sin cambiar CurrentCompany (la sesión sigue en la matriz).
 */
class DelegationWorkplaceController extends Controller
{
    public function __construct(
        private readonly DelegationPerimeter $perimeter,
    ) {}

    public function store(
        DelegationWorkplaceRequest $request,
        Company $delegation,
        CreateWorkplace $action,
    ): RedirectResponse {
        $action->handle($request->validated(), $delegation);

        return back()->with('success', __('centro_trabajo_creado'));
    }

    public function update(
        DelegationWorkplaceRequest $request,
        Company $delegation,
        int $workplace,
        UpdateWorkplace $action,
    ): RedirectResponse {
        $action->handle($this->perimeter->workplace($delegation, $workplace), $request->validated());

        return back()->with('success', __('centro_trabajo_actualizado'));
    }

    public function destroy(Company $delegation, int $workplace, DeleteWorkplace $action): RedirectResponse
    {
        $this->authorize('updateDelegation', $delegation);
        $action->handle($this->perimeter->workplace($delegation, $workplace));

        return back()->with('success', __('centro_trabajo_eliminado'));
    }
}
