<?php

namespace App\Http\Controllers;

use App\Domains\Admin\Actions\RenewCompanyAccount;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Http\Requests\CompanyAccountTableRequest;
use App\Domains\Admin\Http\Requests\RenewCompanyAccountRequest;
use App\Domains\Admin\Http\Resources\CompanyAccountResource;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(CurrentCompany $currentCompany): Response
    {
        $this->authorize('viewAny', CompanyAccount::class);

        $active = CompanyAccount::query()
            ->with('account')
            ->where('status', CompanyAccountStatus::Active)
            ->orderByDesc('start_date')
            ->first();

        $catalog = Account::query()
            ->where('status', AccountStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name', 'rate', 'duration', 'is_default']);

        return Inertia::render('Account/Show', [
            'current' => $active !== null
                ? CompanyAccountResource::make($active)->resolve()
                : null,
            'catalogAccounts' => $catalog->map(fn (Account $account): array => [
                'id' => $account->id,
                'name' => $account->name,
                'rate' => $account->rate,
                'duration' => $account->duration,
                'is_default' => $account->is_default,
            ])->values()->all(),
            'canRenew' => request()->user()?->can('profile.update') === true,
            'companyId' => $currentCompany->id(),
        ]);
    }

    public function table(CompanyAccountTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', CompanyAccount::class);

        $query = CompanyAccount::query()->with('account');

        if ($request->sorts() === []) {
            $query->orderByDesc('start_date')->orderByDesc('id');
        }

        return $table->for($query)
            ->searchable(['status'])
            ->request($request)
            ->through(CompanyAccountResource::class)
            ->json();
    }

    public function renew(
        RenewCompanyAccountRequest $request,
        RenewCompanyAccount $action,
        CurrentCompany $currentCompany,
    ): RedirectResponse {
        $companyId = $currentCompany->id();
        abort_if($companyId === null, 403);

        $company = Company::query()->whereKey($companyId)->firstOrFail();

        $this->authorize('create', CompanyAccount::class);

        $action->handle($company, $request->account());

        return back()->with('success', __('cuenta_empresa_renovada'));
    }
}
