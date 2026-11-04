<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateAccountingAccountType;
use App\Domains\Admin\Actions\DeleteAccountingAccountType;
use App\Domains\Admin\Actions\UpdateAccountingAccountType;
use App\Domains\Admin\Http\Requests\AccountingAccountTypeTableRequest;
use App\Domains\Admin\Http\Requests\StoreAccountingAccountTypeRequest;
use App\Domains\Admin\Http\Requests\UpdateAccountingAccountTypeRequest;
use App\Domains\Admin\Http\Resources\AccountingAccountTypeResource;
use App\Domains\Admin\Models\AccountingAccountType;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AccountingAccountTypeController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', AccountingAccountType::class);

        return Inertia::render('Admin/AccountingAccountTypes/Index');
    }

    public function table(AccountingAccountTypeTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', AccountingAccountType::class);

        $query = AccountingAccountType::query();

        if ($request->sorts() === []) {
            $query->orderBy('code');
        }

        return $table->for($query)
            ->searchable(['code', 'name'])
            ->request($request)
            ->through(AccountingAccountTypeResource::class)
            ->json();
    }

    public function show(): InertiaResponse
    {
        $this->authorize('viewAny', AccountingAccountType::class);

        $rows = AccountingAccountType::query()
            ->orderBy('code')
            ->get(['id', 'autoreference', 'code', 'name', 'mode']);

        $byParent = $rows->groupBy(fn (AccountingAccountType $row): int => (int) $row->autoreference);

        $build = function (int $parentId) use (&$build, $byParent): array {
            return ($byParent->get($parentId) ?? collect())
                ->map(fn (AccountingAccountType $row): array => [
                    'id' => $row->id,
                    'code' => $row->code,
                    'name' => $row->name,
                    'mode' => $row->mode,
                    'level' => $row->level,
                    'children' => $build($row->id),
                ])
                ->values()
                ->all();
        };

        return Inertia::render('Admin/AccountingAccountTypes/Show', [
            'tree' => $build(0),
        ]);
    }

    public function store(StoreAccountingAccountTypeRequest $request, CreateAccountingAccountType $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('grupo_contable_creado'));
    }

    public function update(
        UpdateAccountingAccountTypeRequest $request,
        AccountingAccountType $accountingAccountType,
        UpdateAccountingAccountType $action,
    ): RedirectResponse {
        $action->handle($accountingAccountType, $request->validated());

        return back()->with('success', __('grupo_contable_actualizado'));
    }

    public function destroy(
        AccountingAccountType $accountingAccountType,
        DeleteAccountingAccountType $action,
    ): RedirectResponse {
        $this->authorize('delete', $accountingAccountType);
        $action->handle($accountingAccountType);

        return to_route('admin.accounting-account-types.index')
            ->with('success', __('grupo_contable_eliminado'));
    }
}
