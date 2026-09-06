<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

it('genera artefactos y no sobreescribe sin --force', function () {
    $fs = new Filesystem;
    $domain = 'ScratchGen';
    $entity = 'Probe';
    $paths = [
        app_path("Domains/{$domain}/Models/{$entity}.php"),
        app_path("Domains/{$domain}/Enums/{$entity}Status.php"),
        app_path("Domains/{$domain}/Policies/{$entity}Policy.php"),
    ];

    $cleanup = function () use ($fs, $domain, $entity): void {
        $fs->deleteDirectory(app_path("Domains/{$domain}"));
        $fs->delete(database_path("factories/{$entity}Factory.php"));
        $fs->delete(base_path("tests/Feature/{$domain}/{$entity}Test.php"));
        $fs->delete(resource_path('js/types/'.Str::snake($domain).'.d.ts'));
        $fs->deleteDirectory(resource_path("js/Pages/{$domain}"));

        foreach ($fs->files(database_path('migrations')) as $file) {
            if (str_contains($file->getFilename(), 'create_probes_table')) {
                $fs->delete($file->getPathname());
            }
        }
    };

    $cleanup();

    try {
        $exit = Artisan::call('make:domain-entity', [
            'domain' => $domain,
            'entity' => $entity,
            '--status' => true,
            '--no-frontend' => false,
        ]);

        expect($exit)->toBe(0)
            ->and($fs->exists($paths[0]))->toBeTrue()
            ->and($fs->exists($paths[1]))->toBeTrue()
            ->and($fs->exists($paths[2]))->toBeTrue();

        $original = $fs->get($paths[0]);
        $fs->put($paths[0], $original."\n// marker\n");

        Artisan::call('make:domain-entity', [
            'domain' => $domain,
            'entity' => $entity,
            '--status' => true,
        ]);

        expect($fs->get($paths[0]))->toContain('// marker');
    } finally {
        $cleanup();
    }
});

it('con --global omite tenancy en model/migration/policy', function () {
    $fs = new Filesystem;
    $domain = 'ScratchGlobal';
    $entity = 'CatalogItem';

    $cleanup = function () use ($fs, $domain, $entity): void {
        $fs->deleteDirectory(app_path("Domains/{$domain}"));
        $fs->delete(database_path("factories/{$entity}Factory.php"));
        $fs->delete(base_path("tests/Feature/{$domain}/{$entity}Test.php"));
        $fs->delete(resource_path('js/types/'.Str::snake($domain).'.d.ts'));
        $fs->deleteDirectory(resource_path("js/Pages/{$domain}"));

        foreach ($fs->files(database_path('migrations')) as $file) {
            if (str_contains($file->getFilename(), 'create_catalog_items_table')) {
                $fs->delete($file->getPathname());
            }
        }
    };

    $cleanup();

    try {
        expect(Artisan::call('make:domain-entity', [
            'domain' => $domain,
            'entity' => $entity,
            '--global' => true,
            '--no-frontend' => true,
        ]))->toBe(0);

        $model = $fs->get(app_path("Domains/{$domain}/Models/{$entity}.php"));
        $policy = $fs->get(app_path("Domains/{$domain}/Policies/{$entity}Policy.php"));
        $factory = $fs->get(database_path("factories/{$entity}Factory.php"));
        $test = $fs->get(base_path("tests/Feature/{$domain}/{$entity}Test.php"));

        $migration = collect($fs->files(database_path('migrations')))
            ->first(fn ($f) => str_contains($f->getFilename(), 'create_catalog_items_table'));

        expect($migration)->not->toBeNull();
        $migrationContents = $fs->get($migration->getPathname());

        expect($model)->not->toMatch('/use\s+[^;]*BelongsToCompany/')
            ->and($migrationContents)->not->toContain('company_id')
            ->and($policy)->not->toMatch('/\$\w+->belongsToCurrentCompany\(\)/')
            ->and($factory)->not->toContain("'company_id'")
            ->and($test)->toContain('catálogo global');
    } finally {
        $cleanup();
    }
});
