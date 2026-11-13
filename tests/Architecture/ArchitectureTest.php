<?php

use App\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

arch('Support no conoce los dominios')
    ->expect('App\Support')
    ->not->toUse('App\Domains');

arch('prohibido session helper en Domains')
    ->expect('session')
    ->not->toBeUsedIn('App\Domains');

arch('sin dd/dump/ray')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch('sin env en App (usar config)')
    ->expect('env')
    ->not->toBeUsed('App');

it('todo modelo de negocio en Domains usa BelongsToCompany', function () {
    $fs = new Filesystem;
    $base = app_path('Domains');

    if (! $fs->isDirectory($base)) {
        expect(true)->toBeTrue();

        return;
    }

    $failures = [];

    foreach ($fs->allFiles($base) as $file) {
        if (! preg_match('#[\\\\/]Models[\\\\/]([^\\\\/]+)\.php$#', $file->getPathname(), $m)) {
            continue;
        }

        $relative = Str::after($file->getPathname(), $base.DIRECTORY_SEPARATOR);
        $parts = preg_split('#[\\\\/]#', $relative) ?: [];
        $domain = $parts[0];
        $entity = $m[1];
        $class = "App\\Domains\\{$domain}\\Models\\{$entity}";

        if (! class_exists($class)) {
            require_once $file->getPathname();
        }

        if (! is_subclass_of($class, Model::class)) {
            continue;
        }

        $ignored = [
            'App\\Domains\\Admin\\Models\\Module',
            'App\\Domains\\Admin\\Models\\Functionality',
            // Catálogo geográfico global (--global; c2026-11-04-admin-countries).
            'App\\Domains\\Admin\\Models\\Country',
            'App\\Domains\\Admin\\Models\\Province',
            'App\\Domains\\Admin\\Models\\Town',
            // Catálogo global de monedas (--global; c2026-11-04-admin-currencies).
            'App\\Domains\\Admin\\Models\\Currency',
            // Catálogo global de unidades (--global; c2026-11-04-admin-units).
            'App\\Domains\\Admin\\Models\\Unit',
            // Catálogo global de tipos de IVA (--global; c2026-11-04-admin-vat-types).
            'App\\Domains\\Admin\\Models\\VatType',
            // Catálogo global grupos contables PGC (--global; c2026-11-04-admin-accounting-account-types).
            'App\\Domains\\Admin\\Models\\AccountingAccountType',
            // Catálogo global de bancos (--global; c2026-11-04-banks → Admin).
            'App\\Domains\\Admin\\Models\\Bank',
            // Spatie Teams: plantillas `company_id` NULL + copias locales; no BelongsToCompany.
            'App\\Domains\\Admin\\Models\\Role',
            // Company ES el tenant (no un registro scoped a otro tenant).
            'App\\Domains\\Admin\\Models\\Company',
        ];

        if (in_array($class, $ignored, true)) {
            continue;
        }

        $traits = class_uses_recursive($class);

        if (! isset($traits[BelongsToCompany::class])) {
            $failures[] = $class;
        }
    }

    expect($failures)->toBeEmpty('Modelos sin BelongsToCompany: '.implode(', ', $failures));
});

it('Actions de dominio exponen handle()', function () {
    $fs = new Filesystem;
    $base = app_path('Domains');

    if (! $fs->isDirectory($base)) {
        expect(true)->toBeTrue();

        return;
    }

    $failures = [];

    foreach ($fs->allFiles($base) as $file) {
        if (! preg_match('#[\\\\/]Actions[\\\\/]([^\\\\/]+)\.php$#', $file->getPathname(), $m)) {
            continue;
        }

        $relative = Str::after($file->getPathname(), $base.DIRECTORY_SEPARATOR);
        $parts = preg_split('#[\\\\/]#', $relative) ?: [];
        $domain = $parts[0];
        $action = $m[1];
        $class = "App\\Domains\\{$domain}\\Actions\\{$action}";

        if (! class_exists($class)) {
            require_once $file->getPathname();
        }

        if (! method_exists($class, 'handle')) {
            $failures[] = $class;
        }
    }

    expect($failures)->toBeEmpty('Actions sin handle(): '.implode(', ', $failures));
});

it('controllers de dominio no usan DB facade', function () {
    $fs = new Filesystem;
    $base = app_path('Domains');

    if (! $fs->isDirectory($base)) {
        expect(true)->toBeTrue();

        return;
    }

    $failures = [];

    foreach ($fs->allFiles($base) as $file) {
        if (! preg_match('#[\\\\/]Http[\\\\/]Controllers[\\\\/]#', $file->getPathname())) {
            continue;
        }

        $contents = $fs->get($file->getPathname());

        if (str_contains($contents, 'use Illuminate\\Support\\Facades\\DB')
            || str_contains($contents, 'DB::')) {
            $failures[] = $file->getPathname();
        }
    }

    expect($failures)->toBeEmpty('Controllers con DB: '.implode(', ', $failures));
});

it('FormRequests viven en Http/Requests', function () {
    $fs = new Filesystem;
    $base = app_path('Domains');

    if (! $fs->isDirectory($base)) {
        expect(true)->toBeTrue();

        return;
    }

    foreach ($fs->allFiles($base) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = $fs->get($file->getPathname());

        if (! str_contains($contents, 'extends FormRequest')) {
            continue;
        }

        expect($file->getPathname())->toMatch('#[\\\\/]Http[\\\\/]Requests[\\\\/]#');
    }
});
