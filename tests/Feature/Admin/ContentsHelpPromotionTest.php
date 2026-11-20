<?php

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\HelpContentMap;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\ContentsHelpSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'empresas');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    $this->user->companies()->attach($this->company->id);
    actingWithCompany($this->user, $this->company);
});

it('Field resuelve helpCode override o helpScope.fieldKey', function () {
    $field = (string) file_get_contents(resource_path('js/Components/Form/Field.tsx'));
    $scope = (string) file_get_contents(resource_path('js/Components/Form/FormHelpScope.tsx'));
    $tip = (string) file_get_contents(resource_path('js/Components/HelpTip.tsx'));
    $index = (string) file_get_contents(resource_path('js/Components/Form/index.ts'));

    expect($scope)->toContain('FormHelpScope')
        ->and($index)->toContain('FormHelpScope')
        ->and($field)->toContain('useFormHelpScope')
        ->and($field)->toContain('resolveHelpCode')
        ->and($field)->toContain('helpCode?: string')
        ->and($field)->toContain('fieldKey?: string')
        ->and($tip)->toContain('bg-amber-100')
        ->and($tip)->toContain('return null');
});

it('CompanyForm usa FormHelpScope companies sin helpCode hardcodeado', function () {
    $form = (string) file_get_contents(
        resource_path('js/Pages/Admin/Companies/components/CompanyForm.tsx'),
    );

    expect($form)->toContain('FormHelpScope')
        ->and($form)->toContain('value="companies"')
        ->and($form)->toContain('htmlFor="company-nif"')
        ->and($form)->not->toContain('helpCode="company-nif"')
        ->and($form)->not->toContain('helpCode="companies.nif"');
});

it('ContentsHelpSeeder upserta companies.nif Active sin borrar ajenos', function () {
    Content::factory()->active()->create([
        'code' => 'other-tip',
        'name' => 'Other',
        'title' => ['es' => 'Otro', 'en' => 'Other', 'ca' => 'Altre'],
    ]);

    Content::factory()->create([
        'code' => 'company-nif',
        'name' => 'old name',
        'status' => ContentStatus::Inactive,
        'title' => ['es' => 'Viejo'],
    ]);

    $this->seed(ContentsHelpSeeder::class);

    $nif = Content::query()->where('code', 'companies.nif')->firstOrFail();
    $other = Content::query()->where('code', 'other-tip')->firstOrFail();

    expect(Content::query()->where('code', 'company-nif')->exists())->toBeFalse()
        ->and($nif->kind)->toBe(ContentKind::Help)
        ->and($nif->status)->toBe(ContentStatus::Active)
        ->and($nif->company_id)->toBeNull()
        ->and($nif->getTranslation('title', 'es'))->toBe('NIF de la empresa')
        ->and($nif->getTranslation('title', 'en'))->not->toBe('')
        ->and($other->exists)->toBeTrue();

    $map = app(HelpContentMap::class)->forLocale('es');
    expect($map)->toHaveKey('companies.nif')
        ->and($map['companies.nif']['title'])->toBe('NIF de la empresa')
        ->and($map)->toHaveKey('company-nif')
        ->and($map['company-nif']['title'])->toBe('NIF de la empresa');
});

it('ContentsHelpSeeder restaura companies.nif soft-deleted', function () {
    $trashed = Content::factory()->create([
        'code' => 'companies.nif',
        'name' => 'borrado',
        'status' => ContentStatus::Inactive,
    ]);
    $trashed->delete();

    $this->seed(ContentsHelpSeeder::class);

    $nif = Content::query()->where('code', 'companies.nif')->firstOrFail();

    expect($nif->trashed())->toBeFalse()
        ->and($nif->status)->toBe(ContentStatus::Active)
        ->and($nif->getTranslation('title', 'es'))->toBe('NIF de la empresa');
});

it('Create y Edit empresa exponen help_tips companies.nif tras seeder', function () {
    $this->seed(ContentsHelpSeeder::class);

    $this->get(route('admin.companies.create'))
        ->assertOk()
        ->assertInertia(function (Assert $page) {
            $page->component('Admin/Companies/Create');
            $tips = $page->toArray()['props']['help_tips'] ?? null;
            expect($tips)->toBeArray()
                ->and($tips)->toHaveKey('companies.nif')
                ->and($tips['companies.nif']['title'])->toBe('NIF de la empresa');
        });

    $this->get(route('admin.companies.edit', $this->company))
        ->assertOk()
        ->assertInertia(function (Assert $page) {
            $page->component('Admin/Companies/Edit');
            $tips = $page->toArray()['props']['help_tips'] ?? null;
            expect($tips)->toBeArray()
                ->and($tips)->toHaveKey('companies.nif')
                ->and($tips['companies.nif']['title'])->toBe('NIF de la empresa');
        });
});

it('contents:export-help escribe fixture con companies.nif', function () {
    $this->seed(ContentsHelpSeeder::class);

    $path = storage_path('framework/testing/contents_help_export.json');
    if (is_file($path)) {
        unlink($path);
    }

    $this->artisan('contents:export-help', ['--path' => $path])
        ->assertSuccessful();

    expect(is_file($path))->toBeTrue();

    /** @var list<array<string, mixed>> $decoded */
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    $codes = collect($decoded)->pluck('code')->all();

    expect($codes)->toContain('companies.nif');

    unlink($path);
});
