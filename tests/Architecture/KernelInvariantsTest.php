<?php

use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Models\Concerns\BelongsToCompany;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

it('laravel/boost está en require-dev y Livewire no está en composer', function () {
    $composer = json_decode((string) File::get(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
    $require = $composer['require'] ?? [];
    $requireDev = $composer['require-dev'] ?? [];

    expect($requireDev)->toHaveKey('laravel/boost')
        ->and($require)->not->toHaveKey('livewire/livewire')
        ->and($requireDev)->not->toHaveKey('livewire/livewire');
});

it('no hay componentes Livewire en app/', function () {
    $hits = [];

    foreach ((new Filesystem)->allFiles(app_path()) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = $file->getContents();

        if (str_contains($contents, 'Livewire\\Component')
            || str_contains($contents, 'namespace App\\Http\\Livewire')) {
            $hits[] = $file->getPathname();
        }
    }

    expect($hits)->toBeEmpty('Livewire detectado: '.implode(', ', $hits));
});

it('prohibido session(currentCompany) en app (R-TEN-03)', function () {
    $hits = [];

    foreach ((new Filesystem)->allFiles(app_path()) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (preg_match("/session\\(\\s*['\"]currentCompany['\"]\\s*\\)/", $file->getContents()) === 1) {
            $hits[] = $file->getPathname();
        }
    }

    expect($hits)->toBeEmpty('session(currentCompany) en: '.implode(', ', $hits));
});

it('CurrentCompany existe y Company es el tenant sin BelongsToCompany', function () {
    expect(class_exists(CurrentCompany::class))->toBeTrue()
        ->and(class_uses_recursive(Company::class))->not->toHaveKey(BelongsToCompany::class)
        ->and(class_uses_recursive(Module::class))->not->toHaveKey(BelongsToCompany::class)
        ->and(class_uses_recursive(Functionality::class))->not->toHaveKey(BelongsToCompany::class);
});

it('User expone pertenencia N:M a Company', function () {
    expect(method_exists(User::class, 'companies'))->toBeTrue()
        ->and(method_exists(User::class, 'activeCompanies'))->toBeTrue()
        ->and(method_exists(User::class, 'belongsToCompany'))->toBeTrue();
});

it('existen las tres capas de módulos', function () {
    expect(class_exists(Module::class))->toBeTrue()
        ->and(class_exists(Functionality::class))->toBeTrue()
        ->and(class_exists(CompanyModule::class))->toBeTrue()
        ->and(class_exists(CompanyModuleAuthorization::class))->toBeTrue()
        ->and(class_uses_recursive(CompanyModule::class))->toHaveKey(BelongsToCompany::class)
        ->and(class_uses_recursive(CompanyModuleAuthorization::class))->toHaveKey(BelongsToCompany::class);
});

it('Gate::before documenta bypass Super Admin sin vaciar el perímetro', function () {
    $provider = File::get(app_path('Providers/AppServiceProvider.php'));

    expect($provider)->toContain('Gate::before')
        ->and($provider)->toContain('Super Admin');
});

it('el kernel normativo está versionado', function () {
    expect(File::exists(base_path('docs/standards/kernel.md')))->toBeTrue();
});
