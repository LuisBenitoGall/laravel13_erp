<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreatePaymentMethod;
use App\Domains\Admin\Actions\DeletePaymentMethod;
use App\Domains\Admin\Actions\TogglePaymentMethodStatus;
use App\Domains\Admin\Actions\UpdatePaymentMethod;
use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Domains\Admin\Http\Requests\PaymentMethodTableRequest;
use App\Domains\Admin\Http\Requests\StorePaymentMethodRequest;
use App\Domains\Admin\Http\Requests\TogglePaymentMethodStatusRequest;
use App\Domains\Admin\Http\Requests\UpdatePaymentMethodRequest;
use App\Domains\Admin\Http\Resources\PaymentMethodResource;
use App\Domains\Admin\Models\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Support\I18n\LocaleResolver;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentMethodController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', PaymentMethod::class);

        return Inertia::render('Admin/PaymentMethods/Index');
    }

    public function table(
        PaymentMethodTableRequest $request,
        TableBuilder $table,
        LocaleResolver $locales,
    ): JsonResponse {
        $this->authorize('viewAny', PaymentMethod::class);

        $query = PaymentMethod::query();

        if ($request->sorts() === []) {
            $locale = $locales->resolve($request);
            ActiveFirstOrder::apply($query, PaymentMethodStatus::Active);

            if (DB::getDriverName() === 'sqlite') {
                $query->orderByRaw("json_extract(name, '$.{$locale}')");
            } else {
                $query->orderByRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.{$locale}'))",
                );
            }
        }

        return $table->for($query)
            ->searchable(['name'])
            ->request($request)
            ->through(PaymentMethodResource::class)
            ->json();
    }

    public function store(StorePaymentMethodRequest $request, CreatePaymentMethod $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('metodo_pago_creado'));
    }

    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod,
        UpdatePaymentMethod $action,
    ): RedirectResponse {
        $action->handle($paymentMethod, $request->validated());

        return back()->with('success', __('metodo_pago_actualizado'));
    }

    public function updateStatus(
        TogglePaymentMethodStatusRequest $request,
        PaymentMethod $paymentMethod,
        TogglePaymentMethodStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $paymentMethod = $action->handle(
            $paymentMethod,
            is_string($status) ? PaymentMethodStatus::from($status) : null,
        );

        return response()->json([
            'status' => $paymentMethod->status->value,
        ]);
    }

    public function destroy(PaymentMethod $paymentMethod, DeletePaymentMethod $action): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);
        $action->handle($paymentMethod);

        return to_route('admin.payment-methods.index')
            ->with('success', __('metodo_pago_eliminado'));
    }
}
