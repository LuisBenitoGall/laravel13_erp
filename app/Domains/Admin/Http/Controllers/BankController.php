<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateBank;
use App\Domains\Admin\Actions\DeleteBank;
use App\Domains\Admin\Actions\ToggleBankStatus;
use App\Domains\Admin\Actions\UpdateBank;
use App\Domains\Admin\Enums\BankStatus;
use App\Domains\Admin\Http\Requests\BankTableRequest;
use App\Domains\Admin\Http\Requests\StoreBankRequest;
use App\Domains\Admin\Http\Requests\ToggleBankStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateBankRequest;
use App\Domains\Admin\Http\Resources\BankResource;
use App\Domains\Admin\Models\Bank;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BankController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Bank::class);

        return Inertia::render('Admin/Banks/Index');
    }

    public function table(BankTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Bank::class);

        $query = Bank::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, BankStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'tradename', 'eu_code', 'swift', 'lei', 'supervisor_code'])
            ->request($request)
            ->through(BankResource::class)
            ->json();
    }

    public function store(StoreBankRequest $request, CreateBank $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('banco_creado'));
    }

    public function update(UpdateBankRequest $request, Bank $bank, UpdateBank $action): RedirectResponse
    {
        $action->handle($bank, $request->validated());

        return back()->with('success', __('banco_actualizado'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleBankStatusRequest $request,
        Bank $bank,
        ToggleBankStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $bank = $action->handle(
            $bank,
            is_string($status) ? BankStatus::from($status) : null,
        );

        return response()->json([
            'status' => $bank->status->value,
        ]);
    }

    public function destroy(Bank $bank, DeleteBank $action): RedirectResponse
    {
        $this->authorize('delete', $bank);
        $action->handle($bank);

        return to_route('admin.banks.index')
            ->with('success', __('banco_eliminado'));
    }
}
