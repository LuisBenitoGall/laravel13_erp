<?php

use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\PaymentMethod;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\PaymentMethodsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de métodos de pago', function () {
    $this->get(route('admin.payment-methods.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/PaymentMethods/Index'));
});

it('Index métodos de pago sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/PaymentMethods/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->not->toContain("field: 'code'")
        ->and($source)->not->toContain('name="code"')
        ->and($source)->not->toContain('data.code')
        ->and($source)->not->toContain('accounting_account_id')
        ->and($source)->not->toContain('accounting_account')
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('LangTabs')
        ->and($source)->toContain('persistKey="admin.payment-methods"');
});

it('Index GET no crea filas', function () {
    expect(PaymentMethod::withTrashed()->count())->toBe(0);

    $this->get(route('admin.payment-methods.index'))->assertOk();
    $this->getJson(route('admin.payment-methods.table'))->assertOk();

    expect(PaymentMethod::withTrashed()->count())->toBe(0);
});

it('sin payment-methods.* las rutas responden 403', function () {
    $paymentMethod = PaymentMethod::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.payment-methods.index'))->assertForbidden();
    $this->getJson(route('admin.payment-methods.table'))->assertForbidden();
    $this->post(route('admin.payment-methods.store'), [
        'name' => ['es' => 'Nuevo'],
    ])->assertForbidden();
    $this->put(route('admin.payment-methods.update', $paymentMethod), [
        'name' => ['es' => 'X'],
    ])->assertForbidden();
    $this->patch(route('admin.payment-methods.status', $paymentMethod))->assertForbidden();
    $this->delete(route('admin.payment-methods.destroy', $paymentMethod))->assertForbidden();

    expect(PaymentMethod::query()->count())->toBe(1);
});

it('usuario con payment-methods.view abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('settings.access');
    $user->givePermissionTo(['settings.access', 'payment-methods.view']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.payment-methods.index'))->assertOk();
});

it('seeder deja la Functionality payment-methods con permisos payment-methods.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "payment-methods.{$ability}")->exists())->toBeTrue();
    }
});

it('menú Configuración enlaza Métodos de pago', function () {
    $menu = app(MenuBuilder::class)->forUser($this->user);
    $config = collect($menu)->firstWhere('slug', 'configuracion');
    $item = collect($config['functionalities'] ?? [])->firstWhere('slug', 'payment-methods');

    expect($item)->not->toBeNull()
        ->and($item['label'])->toBe(__('metodos_de_pago'))
        ->and($item['href'])->toBe(route('admin.payment-methods.index', absolute: false));
});

it('crea método de pago con traducciones y status Active', function () {
    $this->post(route('admin.payment-methods.store'), [
        'name' => [
            'es' => 'Transferencia',
            'en' => 'Bank transfer',
            'ca' => 'Transferència',
        ],
    ])->assertRedirect();

    $paymentMethod = PaymentMethod::query()->firstOrFail();

    expect($paymentMethod->status)->toBe(PaymentMethodStatus::Active)
        ->and($paymentMethod->getTranslation('name', 'es'))->toBe('Transferencia')
        ->and($paymentMethod->getTranslation('name', 'en'))->toBe('Bank transfer');

    $this->post(route('admin.payment-methods.store'), [
        'name' => ['en' => 'Only English'],
    ])->assertSessionHasErrors('name.es');
});

it('update cambia nombres', function () {
    $paymentMethod = PaymentMethod::factory()->create([
        'name' => ['es' => 'Viejo', 'en' => 'Old', 'ca' => 'Vell'],
    ]);

    $this->put(route('admin.payment-methods.update', $paymentMethod), [
        'name' => [
            'es' => 'Contado',
            'en' => 'Cash',
            'ca' => 'Comptat',
        ],
    ])->assertRedirect();

    expect($paymentMethod->refresh()->getTranslation('name', 'es'))->toBe('Contado');
});

it('toggle status alterna Active/Inactive', function () {
    $paymentMethod = PaymentMethod::factory()->create();

    $this->patchJson(route('admin.payment-methods.status', $paymentMethod))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.payment-methods.status', $paymentMethod), ['status' => 'active'])
        ->assertOk()
        ->assertJson(['status' => 'active']);

    expect($paymentMethod->refresh()->status)->toBe(PaymentMethodStatus::Active);
});

it('borra método de pago con soft delete', function () {
    $paymentMethod = PaymentMethod::factory()->create();

    $this->delete(route('admin.payment-methods.destroy', $paymentMethod))
        ->assertRedirect(route('admin.payment-methods.index'));

    expect(PaymentMethod::query()->whereKey($paymentMethod->id)->exists())->toBeFalse()
        ->and(PaymentMethod::withTrashed()->whereKey($paymentMethod->id)->exists())->toBeTrue();
});

it('table sin sorts aplica Active-first y luego name', function () {
    PaymentMethod::factory()->create([
        'name' => ['es' => 'Zzz Método Active', 'en' => 'Zzz', 'ca' => 'Zzz'],
        'status' => PaymentMethodStatus::Active,
    ]);
    PaymentMethod::factory()->inactive()->create([
        'name' => ['es' => 'Aaa Método Inactive', 'en' => 'Aaa', 'ca' => 'Aaa'],
    ]);
    PaymentMethod::factory()->create([
        'name' => ['es' => 'Mmm Método Active', 'en' => 'Mmm', 'ca' => 'Mmm'],
        'status' => PaymentMethodStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.payment-methods.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Método']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Método Active',
        'Zzz Método Active',
        'Aaa Método Inactive',
    ]);
});

it('métodos de pago es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('payment_methods', 'company_id'))->toBeFalse();

    PaymentMethod::factory()->create([
        'name' => ['es' => 'Atlantis Pago', 'en' => 'Atlantis', 'ca' => 'Atlantis'],
    ]);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.payment-methods.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Pago');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Pago');
});

it('PaymentMethodsSeeder siembra 12 ids canónicos de forma idempotente', function () {
    expect(Schema::hasColumn('payment_methods', 'code'))->toBeFalse()
        ->and(Schema::hasColumn('payment_methods', 'slug'))->toBeFalse();

    $expectedEs = [
        1 => 'Contado en efectivo',
        2 => 'Cheque/pagaré',
        3 => 'Confirming',
        4 => 'Domiciliación bancaria',
        5 => 'Recibo',
        6 => 'Talón',
        7 => 'Transferencia',
        8 => 'Tarjeta de crédito',
        9 => 'A concretar',
        10 => 'Abono de tarjetas en banco',
        11 => 'Compensación',
        12 => 'Bizum',
    ];

    $this->seed(PaymentMethodsSeeder::class);

    $alien = PaymentMethod::factory()->create([
        'name' => ['es' => 'Método ajeno', 'en' => 'Alien', 'ca' => 'Aliè'],
    ]);

    PaymentMethod::query()->whereKey(1)->delete();

    $this->seed(PaymentMethodsSeeder::class);

    expect(PaymentMethod::query()->whereKey(range(1, 12))->count())->toBe(12)
        ->and(PaymentMethod::query()->whereKey($alien->id)->exists())->toBeTrue()
        ->and(PaymentMethod::query()->where('name->es', 'Transferencia')->count())->toBe(1)
        ->and(PaymentMethod::query()->where('name->es', 'Cheque/pagaré')->count())->toBe(1)
        ->and(PaymentMethod::query()->where('name->es', 'Compensación')->exists())->toBeTrue()
        ->and(PaymentMethod::query()->where('name->es', 'Bizum')->exists())->toBeTrue();

    foreach ($expectedEs as $id => $labelEs) {
        $method = PaymentMethod::query()->whereKey($id)->first();

        expect($method)->not->toBeNull()
            ->and($method->status)->toBe(PaymentMethodStatus::Active)
            ->and($method->getTranslation('name', 'es'))->toBe($labelEs)
            ->and($method->getTranslation('name', 'en'))->not->toBe('')
            ->and($method->getTranslation('name', 'ca'))->not->toBe('');
    }
});
