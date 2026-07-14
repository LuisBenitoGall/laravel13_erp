# Templates backend

> Código canónico comentado. El generador `make:domain-entity` ([tooling.md](tooling.md))
> produce estos esqueletos; este documento es su fuente de verdad. Entidad de ejemplo:
> **Worksite** (obra) en el dominio **Partners**.
> Reglas citadas: [architecture.md](architecture.md).

## 1. Migración

```php
<?php // database/migrations/2026_XX_XX_XXXXXX_create_worksites_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worksites', function (Blueprint $table) {
            $table->id();

            // R-TEN-01: tenant SIEMPRE primero, con FK e índices compuestos.
            $table->foreignId('company_id')->constrained()->restrictOnDelete();

            $table->foreignId('customer_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('commercial_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rate_id')->nullable()->constrained()->nullOnDelete();

            $table->string('code', 32);
            $table->string('name');
            $table->string('status', 32);              // enum PHP en el modelo (R-CAP-06)
            $table->string('address')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            // Importes: decimal SIEMPRE, escala por contexto (R-BD-02).
            $table->decimal('credit_limit', 12, 2)->nullable();

            // R-BD-03: auditoría + soft delete.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índices: compuestos empezando por company_id (R-TEN-01).
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worksites');
    }
};
```

## 2. Enum de estado

```php
<?php // app/Domains/Partners/Enums/WorksiteStatus.php

namespace App\Domains\Partners\Enums;

enum WorksiteStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Closed = 'closed';

    /** Transiciones válidas (R-CAP-06). Fuente única para backend y frontend. */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, match ($this) {
            self::Active => [self::Suspended, self::Closed],
            self::Suspended => [self::Active, self::Closed],
            self::Closed => [],
        }, true);
    }

    public function label(): string
    {
        return __('partners.worksites.status.'.$this->value);
    }
}
```

## 3. Modelo

```php
<?php // app/Domains/Partners/Models/Worksite.php

namespace App\Domains\Partners\Models;

use App\Domains\Admin\Models\User;
use App\Domains\Catalog\Models\Rate;
use App\Domains\Partners\Enums\WorksiteStatus;
use App\Support\Models\Concerns\Auditable;        // created_by / updated_by
use App\Support\Models\Concerns\BelongsToCompany; // global scope tenant (R-TEN-02)
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worksite extends Model
{
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    // Whitelist explícita; company_id NUNCA es fillable (lo pone el trait).
    protected $fillable = [
        'customer_company_id', 'commercial_id', 'rate_id', 'code', 'name',
        'status', 'address', 'starts_at', 'ends_at', 'credit_limit',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorksiteStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
            'credit_limit' => 'decimal:2',   // escala = columna (R-BD-02)
        ];
    }

    // ── Relaciones (los modelos son finos: nada de lógica de negocio, R-CAP-03)
    public function customer() { return $this->belongsTo(Company::class, 'customer_company_id'); }
    public function commercial() { return $this->belongsTo(User::class, 'commercial_id'); }
    public function rate() { return $this->belongsTo(Rate::class); }

    // ── Scopes nombrados
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', WorksiteStatus::Active);
    }
}
```

## 4. Factory

```php
<?php // database/factories/WorksiteFactory.php

namespace Database\Factories;

use App\Domains\Partners\Enums\WorksiteStatus;
use App\Domains\Partners\Models\{Company, Worksite};
use Illuminate\Database\Eloquent\Factories\Factory;

class WorksiteFactory extends Factory
{
    protected $model = Worksite::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),          // el test decide el tenant con for()
            'customer_company_id' => Company::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('OBR-####')),
            'name' => $this->faker->company().' — '.$this->faker->streetName(),
            'status' => WorksiteStatus::Active,
            'starts_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }

    public function closed(): static
    {
        return $this->state(['status' => WorksiteStatus::Closed]);
    }
}
```

## 5. Policy

```php
<?php // app/Domains/Partners/Policies/WorksitePolicy.php

namespace App\Domains\Partners\Policies;

use App\Domains\Admin\Models\User;
use App\Domains\Partners\Models\Worksite;

class WorksitePolicy
{
    // Patrón fijo: (a) permiso Spatie <módulo>.<acción> (R-AUT-02)
    //              (b) pertenencia al tenant activo en métodos con modelo (R-AUT-01).
    // El bypass de Super Admin vive en Gate::before (R-AUT-03), no aquí.

    public function viewAny(User $user): bool
    {
        return $user->can('worksites.view');
    }

    public function view(User $user, Worksite $worksite): bool
    {
        return $user->can('worksites.view') && $worksite->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('worksites.create');
    }

    public function update(User $user, Worksite $worksite): bool
    {
        return $user->can('worksites.edit') && $worksite->belongsToCurrentCompany();
    }

    public function delete(User $user, Worksite $worksite): bool
    {
        return $user->can('worksites.delete') && $worksite->belongsToCurrentCompany();
    }

    /** Habilidad de ciclo de vida — una por transición relevante. */
    public function close(User $user, Worksite $worksite): bool
    {
        return $user->can('worksites.close') && $worksite->belongsToCurrentCompany();
    }
}
```

## 6. FormRequests

```php
<?php // app/Domains/Partners/Http/Requests/StoreWorksiteRequest.php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Partners\Models\Worksite;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorksiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Worksite::class); // delega en la Policy (R-CAP-04)
    }

    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            // R-TEN-07: exists/unique SIEMPRE acotados al tenant.
            'code' => ['required', 'string', 'max:32',
                Rule::unique('worksites')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'customer_company_id' => ['required',
                Rule::exists('customer_providers', 'customer_id')->where('provider_id', $companyId)],
            'commercial_id' => ['nullable', Rule::exists('users', 'id')],
            'rate_id' => ['nullable', Rule::exists('rates', 'id')->where('company_id', $companyId)],
            'address' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'credit_limit' => ['nullable', 'decimal:0,2', 'min:0'],
        ];
    }
}
// UpdateWorksiteRequest: igual, con unique()->ignore($this->worksite) y can('update', $this->worksite).
```

## 7. Action

```php
<?php // app/Domains/Partners/Actions/CreateWorksite.php

namespace App\Domains\Partners\Actions;

use App\Domains\Partners\Events\WorksiteCreated;
use App\Domains\Partners\Models\Worksite;
use Illuminate\Support\Facades\DB;

/** Un caso de uso = una Action (R-CAP-02). Único punto de escritura de la entidad. */
class CreateWorksite
{
    public function handle(array $data): Worksite
    {
        return DB::transaction(function () use ($data) {
            $worksite = Worksite::create($data);

            // Si la entidad se numerase: app(NumberingService::class)->next('worksite')
            // DENTRO de esta transacción (R-BD-05).

            // Eventos al final, con IDs — no modelos vivos (R-DOM-04).
            WorksiteCreated::dispatch($worksite->id, $worksite->company_id);

            return $worksite;
        });
    }
}
```

## 8. TableRequest + Controller

```php
<?php // app/Domains/Partners/Http/Requests/WorksiteTableRequest.php

namespace App\Domains\Partners\Http\Requests;

use App\Support\Table\TableRequest;

class WorksiteTableRequest extends TableRequest
{
    // Whitelists del contrato de tabla (frontend.md §3.1): nada fuera de aquí llega al query.
    protected array $sortable = ['code', 'name', 'status', 'starts_at'];
    protected array $filterable = ['status', 'customer_company_id', 'commercial_id', 'search'];
}
```

```php
<?php // app/Domains/Partners/Http/Controllers/WorksiteController.php

namespace App\Domains\Partners\Http\Controllers;

use App\Domains\Partners\Actions\{CreateWorksite, DeleteWorksite, UpdateWorksite};
use App\Domains\Partners\Http\Requests\{StoreWorksiteRequest, UpdateWorksiteRequest, WorksiteTableRequest};
use App\Domains\Partners\Http\Resources\WorksiteResource;
use App\Domains\Partners\Models\Worksite;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\{Inertia, Response};

/** Controller delgado (R-CAP-01): authorize → validated → Action → respuesta. */
class WorksiteController
{
    public function index(): Response
    {
        $this->authorize('viewAny', Worksite::class);

        return Inertia::render('Partners/Worksites/Index');
    }

    /** Endpoint del contrato Tabulator (frontend.md §3.1). */
    public function table(WorksiteTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Worksite::class);

        return $table->for(Worksite::query()->with(['customer', 'commercial']))
            ->searchable(['code', 'name'])       // filtro 'search' → LIKE sobre estas columnas
            ->request($request)
            ->through(WorksiteResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('create', Worksite::class);

        return Inertia::render('Partners/Worksites/Create', [
            'options' => WorksiteResource::formOptions(), // selects: clientes, tarifas...
        ]);
    }

    public function store(StoreWorksiteRequest $request, CreateWorksite $action): RedirectResponse
    {
        $worksite = $action->handle($request->validated());

        // Patrón de flujo: tras store → Edit con toast (frontend.md §5).
        return to_route('partners.worksites.edit', $worksite)
            ->with('success', __('partners.worksites.created'));
    }

    public function edit(Worksite $worksite): Response
    {
        $this->authorize('update', $worksite);

        return Inertia::render('Partners/Worksites/Edit', [
            'worksite' => WorksiteResource::make($worksite->load(['customer', 'rate'])),
            'options' => WorksiteResource::formOptions(),
        ]);
    }

    public function update(UpdateWorksiteRequest $request, Worksite $worksite, UpdateWorksite $action): RedirectResponse
    {
        $action->handle($worksite, $request->validated());

        return back()->with('success', __('partners.worksites.updated'));
    }

    public function destroy(Worksite $worksite, DeleteWorksite $action): RedirectResponse
    {
        $this->authorize('delete', $worksite);
        $action->handle($worksite);

        return to_route('partners.worksites.index')
            ->with('success', __('partners.worksites.deleted'));
    }
}
```

## 9. Resource

```php
<?php // app/Domains/Partners/Http/Resources/WorksiteResource.php

namespace App\Domains\Partners\Http\Resources;

use App\Domains\Partners\Models\Worksite;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Worksite — salida explícita, nunca el modelo crudo (R-CAP-05). */
class WorksiteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id, 'name' => $this->customer->name,
            ]),
            'commercial_name' => $this->commercial?->name,
            'starts_at' => $this->starts_at?->toDateString(),
            'credit_limit' => $this->credit_limit,
            'can' => [ // permisos por fila → rowActions del ServerTable
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],
        ];
    }
}
```

## 10. Rutas

```php
<?php // routes/domains/partners.php

use App\Domains\Partners\Http\Controllers\WorksiteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'current_company', 'module:partners'])
    ->prefix('partners')->name('partners.')
    ->group(function () {
        // El endpoint table SIEMPRE antes del resource, hermano del index (frontend.md §3.1).
        Route::get('worksites/table', [WorksiteController::class, 'table'])->name('worksites.table');
        Route::resource('worksites', WorksiteController::class)->except('show');
    });
```

## 11. Tests (Pest)

```php
<?php // tests/Feature/Partners/WorksiteTest.php

use App\Domains\Partners\Models\{Company, Worksite};
use App\Domains\Admin\Models\User;

use function Pest\Laravel\{actingAs, get, post};

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->for($this->company)->withPermissions([
        'worksites.view', 'worksites.create', 'worksites.edit', 'worksites.delete',
    ])->create();
    actingAs($this->user)->withCurrentCompany($this->company);
});

it('lists worksites of the current company', function () {
    Worksite::factory()->for($this->company)->count(3)->create();

    get(route('partners.worksites.table'))
        ->assertOk()
        ->assertJsonPath('total', 3);
});

it('creates a worksite', function () {
    post(route('partners.worksites.store'), Worksite::factory()->raw())
        ->assertRedirect();

    expect(Worksite::count())->toBe(1);
});

it('rejects users without permission', function () {
    $this->user->revokePermissionTo('worksites.create');

    post(route('partners.worksites.store'), Worksite::factory()->raw())
        ->assertForbidden();
});

// ── R-TEN-05: test de aislamiento OBLIGATORIO en toda entidad. Sin esto no hay DoD.
it('never exposes worksites from another company', function () {
    $other = Worksite::factory()->create(); // otra empresa (factory crea la suya)

    get(route('partners.worksites.table'))->assertJsonPath('total', 0);
    get(route('partners.worksites.edit', $other))->assertNotFound(); // el global scope lo oculta
});
```

## 12. Piezas de soporte referenciadas (se implementan en Fase 0)

| Pieza | Ubicación | Contrato |
|---|---|---|
| `BelongsToCompany` | `app/Support/Models/Concerns` | Global scope por `CurrentCompany` + fill de `company_id` en creating + `belongsToCurrentCompany()` |
| `Auditable` | `app/Support/Models/Concerns` | Rellena `created_by`/`updated_by` |
| `CurrentCompany` | `app/Support/Tenancy` | Singleton por request; `id()`, `get()`, `set()`; middleware `current_company` |
| `TableRequest` / `TableBuilder` | `app/Support/Table` | Contrato de tablas (frontend.md §3.1) |
| `NumberingService` | `app/Domains/Admin` | `next(string $docType): DocumentNumber` con FOR UPDATE (R-BD-05) |
| `EnsureModuleIsActive` (`module:`) | `app/Http/Middleware` | 403/redirect si el módulo no está activo para la empresa |
| Helper Pest `withCurrentCompany()` | `tests/Pest.php` | Fija el tenant activo del test |
