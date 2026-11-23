<?php

namespace App\Domains\HR\Http\Controllers;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Workplace;
use App\Domains\HR\Actions\CreateEmployee;
use App\Domains\HR\Actions\DeleteEmployee;
use App\Domains\HR\Actions\EndEmployee;
use App\Domains\HR\Actions\RenewEmployee;
use App\Domains\HR\Actions\UpdateEmployee;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Http\Requests\EmployeeTableRequest;
use App\Domains\HR\Http\Requests\EndEmployeeRequest;
use App\Domains\HR\Http\Requests\RenewEmployeeRequest;
use App\Domains\HR\Http\Requests\StoreEmployeeRequest;
use App\Domains\HR\Http\Requests\UpdateEmployeeRequest;
use App\Domains\HR\Http\Resources\EmployeeResource;
use App\Domains\HR\Models\ContractType;
use App\Domains\HR\Models\Employee;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class EmployeeController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Employee::class);

        $contractTypes = ContractType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('HR/Employees/Index', [
            'contract_type_filter' => $contractTypes
                ->mapWithKeys(fn (ContractType $type): array => [(string) $type->id => $type->name])
                ->all(),
        ]);
    }

    public function table(EmployeeTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::query()->with(['user', 'contractType', 'workplace']);

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, EmployeeStatus::Active)->orderByDesc('start_date');
        }

        return $table->for($query)
            ->searchable(['job_title'])
            ->filterUsing('user_name', function ($builder, string $type, mixed $value): void {
                if ($type !== 'like' || ! is_string($value) || $value === '') {
                    return;
                }

                $like = '%'.$value.'%';
                $builder->whereHas('user', function ($userQuery) use ($like): void {
                    $userQuery->where(function ($inner) use ($like): void {
                        $inner->where('name', 'like', $like)
                            ->orWhere('surname', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
                });
            })
            ->request($request)
            ->through(EmployeeResource::class)
            ->json();
    }

    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', Employee::class);

        $userId = $request->integer('user_id');

        return Inertia::render('HR/Employees/Create', [
            'options' => $this->options(),
            'default_user_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function store(StoreEmployeeRequest $request, CreateEmployee $action): RedirectResponse
    {
        $employee = $action->handle($request->validated());

        return to_route('hr.employees.edit', $employee)
            ->with('success', __('empleo_creado'));
    }

    public function edit(Employee $employee): InertiaResponse
    {
        $this->authorize('update', $employee);

        $employee->load(['user', 'contractType', 'workplace']);

        $history = Employee::query()
            ->with(['user', 'contractType', 'workplace'])
            ->where('user_id', $employee->user_id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('HR/Employees/Edit', [
            'employee' => EmployeeResource::make($employee)->resolve(),
            'history' => EmployeeResource::collection($history)->resolve(),
            'options' => $this->options($employee),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployee $action): RedirectResponse
    {
        $action->handle($employee, $request->validated());

        return back()->with('success', __('empleo_actualizado'));
    }

    public function renewForm(Employee $employee): InertiaResponse
    {
        $this->authorize('renew', $employee);

        $employee->load(['user', 'contractType', 'workplace']);

        return Inertia::render('HR/Employees/Renew', [
            'employee' => EmployeeResource::make($employee)->resolve(),
            'options' => $this->options($employee),
        ]);
    }

    public function renew(RenewEmployeeRequest $request, Employee $employee, RenewEmployee $action): RedirectResponse
    {
        $renewed = $action->handle($employee, $request->validated());

        return to_route('hr.employees.edit', $renewed)
            ->with('success', __('empleo_renovado'));
    }

    public function end(EndEmployeeRequest $request, Employee $employee, EndEmployee $action): RedirectResponse
    {
        $action->handle($employee, $request->validated('end_date'));

        return back()->with('success', __('empleo_cerrado'));
    }

    public function destroy(Employee $employee, DeleteEmployee $action): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $action->handle($employee);

        return to_route('hr.employees.index')
            ->with('success', __('empleo_eliminado'));
    }

    /**
     * Opciones de selects: miembros `user_companies`, tipos Active (no borrados)
     * y centros Active. El valor actual de $employee se incluye aunque esté
     * inactivo/borrado para no romper el Edit de periodos históricos.
     *
     * @return array{
     *     users: list<array{id: int, name: string}>,
     *     contract_types: list<array{id: int, name: string, duration: int|null}>,
     *     workplaces: list<array{id: int, name: string}>
     * }
     */
    private function options(?Employee $employee = null): array
    {
        $companyId = app(CurrentCompany::class)->id();

        $users = User::query()
            ->whereHas('companies', fn ($query) => $query->where('companies.id', $companyId))
            ->orderBy('name')
            ->get(['users.id', 'users.name']);

        $contractTypes = ContractType::query()
            ->withTrashed()
            ->where(function ($query) use ($employee): void {
                $query->where(function ($inner): void {
                    $inner->whereNull('deleted_at')->where('status', 'active');
                })->when(
                    $employee !== null,
                    fn ($q) => $q->orWhere('id', $employee?->contract_type_id),
                );
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $workplaces = Workplace::query()
            ->withTrashed()
            ->where(function ($query) use ($employee): void {
                $query->where(function ($inner): void {
                    $inner->whereNull('deleted_at')->where('status', WorkplaceStatus::Active);
                })->when(
                    $employee?->workplace_id !== null,
                    fn ($q) => $q->orWhere('id', $employee?->workplace_id),
                );
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'users' => $users->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])->all(),
            'contract_types' => $contractTypes->map(fn (ContractType $type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'duration' => $type->duration,
            ])->all(),
            'workplaces' => $workplaces->map(fn (Workplace $workplace): array => ['id' => $workplace->id, 'name' => $workplace->name])->all(),
        ];
    }
}
