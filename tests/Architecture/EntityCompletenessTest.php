<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

it('pasa con conjunto vacío de modelos de dominio o solo catálogos globales', function () {
    $base = app_path('Domains');
    $files = (new Filesystem)->exists($base)
        ? collect((new Filesystem)->allFiles($base))
            ->filter(fn ($f) => preg_match('#[\\\\/]Models[\\\\/][^\\\\/]+\.php$#', $f->getPathname()))
            ->reject(fn ($f) => str_contains($f->getPathname(), 'Admin'.DIRECTORY_SEPARATOR.'Models'))
        : collect();

    expect($files)->toBeEmpty();
});

it('señala artefactos ausentes cuando hay un modelo incompleto', function () {
    $fs = new Filesystem;
    $domain = 'ScratchAudit';
    $entity = 'Widget';
    $modelPath = app_path("Domains/{$domain}/Models/{$entity}.php");

    $fs->ensureDirectoryExists(dirname($modelPath));
    $fs->put($modelPath, "<?php\nnamespace App\\Domains\\{$domain}\\Models;\nclass {$entity} extends \\Illuminate\\Database\\Eloquent\\Model {}\n");

    try {
        $missing = [];
        foreach ([
            'Policy' => app_path("Domains/{$domain}/Policies/{$entity}Policy.php"),
            'Factory' => database_path("factories/{$entity}Factory.php"),
            'Controller' => app_path("Domains/{$domain}/Http/Controllers/{$entity}Controller.php"),
        ] as $label => $path) {
            if (! $fs->exists($path)) {
                $missing[] = $path;
            }
        }

        expect($missing)->not->toBeEmpty()
            ->and($missing[0])->toContain('Policy');
    } finally {
        $fs->deleteDirectory(app_path("Domains/{$domain}"));
    }
});

/**
 * Backstop CI: recorre app/Domains Models y exige artefactos Siempre.
 */
it('EntityCompleteness: todos los modelos de dominio tienen artefactos Siempre', function () {
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
        $entityPlural = Str::pluralStudly($entity);
        $domainSnake = Str::snake($domain);

        $required = [
            app_path("Domains/{$domain}/Policies/{$entity}Policy.php"),
            database_path("factories/{$entity}Factory.php"),
            app_path("Domains/{$domain}/Http/Requests/Store{$entity}Request.php"),
            app_path("Domains/{$domain}/Http/Requests/Update{$entity}Request.php"),
            app_path("Domains/{$domain}/Http/Requests/{$entity}TableRequest.php"),
            app_path("Domains/{$domain}/Http/Resources/{$entity}Resource.php"),
            app_path("Domains/{$domain}/Http/Controllers/{$entity}Controller.php"),
            base_path("tests/Feature/{$domain}/{$entity}Test.php"),
            resource_path("js/types/{$domainSnake}.d.ts"),
            resource_path("js/Pages/{$domain}/{$entityPlural}/Index.tsx"),
        ];

        foreach ($required as $path) {
            if (! $fs->exists($path)) {
                $failures[] = $path;
            }
        }
    }

    expect($failures)->toBeEmpty('Artefactos ausentes: '.implode(', ', $failures));
});
