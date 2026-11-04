<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateCurrency;
use App\Domains\Admin\Actions\DeleteCurrency;
use App\Domains\Admin\Actions\ToggleCurrencyStatus;
use App\Domains\Admin\Actions\UpdateCurrency;
use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Http\Requests\CurrencyTableRequest;
use App\Domains\Admin\Http\Requests\StoreCurrencyRequest;
use App\Domains\Admin\Http\Requests\ToggleCurrencyStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateCurrencyRequest;
use App\Domains\Admin\Http\Resources\CurrencyResource;
use App\Domains\Admin\Models\Currency;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CurrencyController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Currency::class);

        return Inertia::render('Admin/Currencies/Index');
    }

    public function table(CurrencyTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Currency::class);

        $query = Currency::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, CurrencyStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'code', 'number', 'symbol'])
            ->request($request)
            ->through(CurrencyResource::class)
            ->json();
    }

    public function store(StoreCurrencyRequest $request, CreateCurrency $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('moneda_creada'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency, UpdateCurrency $action): RedirectResponse
    {
        $action->handle($currency, $request->validated());

        return back()->with('success', __('moneda_actualizada'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleCurrencyStatusRequest $request,
        Currency $currency,
        ToggleCurrencyStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $currency = $action->handle(
            $currency,
            is_string($status) ? CurrencyStatus::from($status) : null,
        );

        return response()->json([
            'status' => $currency->status->value,
        ]);
    }

    public function destroy(Currency $currency, DeleteCurrency $action): RedirectResponse
    {
        $this->authorize('delete', $currency);
        $action->handle($currency);

        return to_route('admin.currencies.index')
            ->with('success', __('moneda_eliminada'));
    }
}
