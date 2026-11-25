<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DeleteExchangeRate;
use App\Domains\Admin\Actions\UpsertExchangeRatePair;
use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Http\Requests\ExchangeRateTableRequest;
use App\Domains\Admin\Http\Requests\StoreExchangeRateRequest;
use App\Domains\Admin\Http\Requests\UpdateExchangeRateRequest;
use App\Domains\Admin\Http\Resources\CurrencyResource;
use App\Domains\Admin\Http\Resources\ExchangeRateResource;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\ExchangeRate;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExchangeRateController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', ExchangeRate::class);

        return Inertia::render('Admin/ExchangeRates/Index', [
            'currencies' => CurrencyResource::collection(
                Currency::query()
                    ->where('status', CurrencyStatus::Active)
                    ->orderBy('name')
                    ->get()
            )->resolve(),
        ]);
    }

    public function table(ExchangeRateTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', ExchangeRate::class);

        // CASE portable (MySQL + SQLite): A = menor id, B = mayor id del par.
        $minId = 'CASE WHEN from_currency_id < to_currency_id THEN from_currency_id ELSE to_currency_id END';
        $maxId = 'CASE WHEN from_currency_id > to_currency_id THEN from_currency_id ELSE to_currency_id END';
        $minEr = 'CASE WHEN exchange_rates.from_currency_id < exchange_rates.to_currency_id THEN exchange_rates.from_currency_id ELSE exchange_rates.to_currency_id END';
        $maxEr = 'CASE WHEN exchange_rates.from_currency_id > exchange_rates.to_currency_id THEN exchange_rates.from_currency_id ELSE exchange_rates.to_currency_id END';

        // Una fila = par unordered; ambas tasas vía JOIN.
        $query = ExchangeRate::query()
            ->whereIn('exchange_rates.id', function ($sub) use ($minId, $maxId): void {
                $sub->selectRaw('MIN(id)')
                    ->from('exchange_rates')
                    ->whereNull('deleted_at')
                    ->groupByRaw("{$minId}, {$maxId}");
            })
            ->leftJoin('currencies as ca', function ($join) use ($minEr): void {
                $join->whereRaw("ca.id = {$minEr}");
            })
            ->leftJoin('currencies as cb', function ($join) use ($maxEr): void {
                $join->whereRaw("cb.id = {$maxEr}");
            })
            ->leftJoin('exchange_rates as ab', function ($join) use ($minEr, $maxEr): void {
                $join->whereRaw("ab.from_currency_id = {$minEr}")
                    ->whereRaw("ab.to_currency_id = {$maxEr}")
                    ->whereNull('ab.deleted_at');
            })
            ->leftJoin('exchange_rates as ba', function ($join) use ($minEr, $maxEr): void {
                $join->whereRaw("ba.from_currency_id = {$maxEr}")
                    ->whereRaw("ba.to_currency_id = {$minEr}")
                    ->whereNull('ba.deleted_at');
            })
            ->select('exchange_rates.*')
            ->selectRaw("{$minEr} as currency_a_id")
            ->selectRaw("{$maxEr} as currency_b_id")
            ->selectRaw('ca.code as code_a')
            ->selectRaw('cb.code as code_b')
            ->selectRaw('ab.rate as rate_ab')
            ->selectRaw('ba.rate as rate_ba')
            ->selectRaw(
                (DB::connection()->getDriverName() === 'sqlite'
                    ? "(COALESCE(ca.code, '') || '/' || COALESCE(cb.code, ''))"
                    : "CONCAT(COALESCE(ca.code, ''), '/', COALESCE(cb.code, ''))"
                ).' as pair_label'
            );

        if ($request->sorts() === []) {
            $query->orderBy('pair_label');
        }

        return $table->for($query)
            ->searchable(['ca.code', 'cb.code'])
            ->request($request)
            ->through(ExchangeRateResource::class)
            ->json();
    }

    public function store(StoreExchangeRateRequest $request, UpsertExchangeRatePair $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('conversion_moneda_creada'));
    }

    public function update(
        UpdateExchangeRateRequest $request,
        ExchangeRate $exchangeRate,
        UpsertExchangeRatePair $action,
    ): RedirectResponse {
        $data = $request->validated();
        // Canonical A/B (min/max id) para que rate_ab / rate_ba del form coincidan con el Index.
        $data['from_currency_id'] = min($exchangeRate->from_currency_id, $exchangeRate->to_currency_id);
        $data['to_currency_id'] = max($exchangeRate->from_currency_id, $exchangeRate->to_currency_id);

        $action->handle($data);

        return back()->with('success', __('conversion_moneda_actualizada'));
    }

    public function destroy(ExchangeRate $exchangeRate, DeleteExchangeRate $action): RedirectResponse
    {
        $this->authorize('delete', $exchangeRate);
        $action->handle($exchangeRate);

        return to_route('admin.exchange-rates.index')
            ->with('success', __('conversion_moneda_eliminada'));
    }
}
