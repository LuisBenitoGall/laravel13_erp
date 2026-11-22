<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateAccount;
use App\Domains\Admin\Actions\DeleteAccount;
use App\Domains\Admin\Actions\ToggleAccountStatus;
use App\Domains\Admin\Actions\UpdateAccount;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Http\Requests\AccountTableRequest;
use App\Domains\Admin\Http\Requests\StoreAccountRequest;
use App\Domains\Admin\Http\Requests\ToggleAccountStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateAccountRequest;
use App\Domains\Admin\Http\Resources\AccountResource;
use App\Domains\Admin\Models\Account;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AccountController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Account::class);

        return Inertia::render('Admin/Accounts/Index');
    }

    public function renewal(): RedirectResponse
    {
        $this->authorize('viewAny', Account::class);

        return redirect()->route('admin.account.renew');
    }

    public function table(AccountTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Account::class);

        $query = Account::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, AccountStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'description'])
            ->request($request)
            ->through(AccountResource::class)
            ->json();
    }

    public function store(StoreAccountRequest $request, CreateAccount $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('cuenta_creada'));
    }

    public function update(UpdateAccountRequest $request, Account $account, UpdateAccount $action): RedirectResponse
    {
        $action->handle($account, $request->validated());

        return back()->with('success', __('cuenta_actualizada'));
    }

    public function updateStatus(
        ToggleAccountStatusRequest $request,
        Account $account,
        ToggleAccountStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $account = $action->handle(
            $account,
            is_string($status) ? AccountStatus::from($status) : null,
        );

        return response()->json([
            'status' => $account->status->value,
        ]);
    }

    public function destroy(Account $account, DeleteAccount $action): RedirectResponse
    {
        $this->authorize('delete', $account);
        $action->handle($account);

        return to_route('admin.accounts.index')
            ->with('success', __('cuenta_eliminada'));
    }
}
