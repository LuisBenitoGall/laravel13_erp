<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Note;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    $this->otherCompany = Company::factory()->create();

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    $this->user->companies()->attach([$this->company->id, $this->otherCompany->id]);

    actingWithCompany($this->user, $this->company);
});

it('crea nota personal sin company_id', function () {
    $this->post(route('admin.notes.store'), [
        'title' => 'Recordatorio',
        'note' => 'Comprobar facturas',
    ])->assertRedirect();

    $note = Note::query()->where('user_id', $this->user->id)->firstOrFail();

    expect($note->title)->toBe('Recordatorio')
        ->and($note->note)->toBe('Comprobar facturas')
        ->and($note->getAttributes())->not->toHaveKey('company_id');
});

it('rechaza note de más de 1000 caracteres', function () {
    $this->post(route('admin.notes.store'), [
        'title' => 'Largo',
        'note' => str_repeat('a', 1001),
    ])->assertSessionHasErrors('note');

    expect(Note::query()->where('user_id', $this->user->id)->count())->toBe(0);

    $ok = str_repeat('b', 1000);
    $this->post(route('admin.notes.store'), [
        'title' => 'Límite',
        'note' => $ok,
    ])->assertRedirect();

    expect(Note::query()->where('user_id', $this->user->id)->value('note'))->toBe($ok);
});

it('bloquea el tope de 50 notas por user', function () {
    Note::factory()->count(Note::MAX_PER_USER)->create([
        'user_id' => $this->user->id,
    ]);

    $this->post(route('admin.notes.store'), [
        'title' => 'Extra',
        'note' => 'No debería guardarse',
    ])->assertSessionHasErrors('title');

    expect(Note::query()->where('user_id', $this->user->id)->count())
        ->toBe(Note::MAX_PER_USER);
});

it('dashboard lista notas del user y no filtra por company', function () {
    Note::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Personal',
        'note' => 'Cuerpo',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('notes', 1)
            ->where('notes.0.title', 'Personal'));

    actingWithCompany($this->user, $this->otherCompany);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('notes', 1)
            ->where('notes.0.title', 'Personal'));
});

it('elimina nota propia y aísla destroy cruzado', function () {
    $owner = User::factory()->create();
    $owner->companies()->attach($this->company->id);
    actingWithCompany($owner, $this->company);

    $own = Note::factory()->create([
        'user_id' => $owner->id,
        'title' => 'Mía',
    ]);

    $otherUser = User::factory()->create();
    $otherUser->companies()->attach($this->company->id);
    $foreign = Note::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Ajena',
    ]);

    $this->delete(route('admin.notes.destroy', $own))->assertRedirect();
    expect(Note::query()->whereKey($own->id)->exists())->toBeFalse();

    $this->delete(route('admin.notes.destroy', $foreign))->assertForbidden();
});

it('comparte notes_count en Inertia', function () {
    Note::factory()->count(2)->create(['user_id' => $this->user->id]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notes_count', 2));
});

it('NotesModal sustituye el stub vacío', function () {
    $source = (string) file_get_contents(resource_path('js/Components/Topbar/NotesModal.tsx'));
    $topbar = (string) file_get_contents(resource_path('js/Components/Topbar/AppTopbar.tsx'));

    expect($source)->toContain('nueva_nota')
        ->and($source)->toContain('/admin/notes')
        ->and($source)->toContain('ver_todas_las_notas')
        ->and($topbar)->toContain('NotesModal')
        ->and($topbar)->not->toContain('NotesStub')
        ->and(file_exists(resource_path('js/Components/Topbar/NotesStub.tsx')))->toBeFalse();
});

it('dashboard no renderiza empty state de notas', function () {
    $dashboard = (string) file_get_contents(resource_path('js/Pages/Dashboard.tsx'));
    $css = (string) file_get_contents(resource_path('css/app.css'));

    expect($dashboard)->toContain('notes.length > 0')
        ->and($dashboard)->not->toContain("t('sin_notas')")
        ->and($dashboard)->toContain('hour: \'2-digit\'')
        ->and($dashboard)->toContain('Trash2')
        ->and($dashboard)->toContain('line-clamp-')
        ->and($css)->toContain('background: #ffc')
        ->and($css)->toContain('transform: rotate');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('notes', 0));
});
