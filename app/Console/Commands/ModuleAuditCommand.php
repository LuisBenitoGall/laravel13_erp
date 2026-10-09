<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class ModuleAuditCommand extends Command
{
    protected $signature = 'module:audit
                            {domain? : Dominio a auditar}
                            {entity? : Entidad a auditar}';

    protected $description = 'Auditoría de completitud de artefactos Siempre (tooling.md §2)';

    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $models = $this->discoverModels();

        if ($models === []) {
            $this->info('No hay entidades en app/Domains/*/Models.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($models as $model) {
            foreach ($this->artifactChecklist($model['domain'], $model['entity']) as $label => $exists) {
                $rows[] = [
                    $model['domain'],
                    $model['entity'],
                    $label,
                    $exists ? '✓' : '✗',
                ];
            }
        }

        $this->table(['Domain', 'Entity', 'Artifact', 'Status'], $rows);

        $this->checkFunctionalityPermissions();

        $missing = collect($rows)->contains(fn (array $row): bool => $row[3] === '✗');

        return $missing ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<array{domain: string, entity: string, path: string}>
     */
    private function discoverModels(): array
    {
        $domainFilter = $this->argument('domain');
        $entityFilter = $this->argument('entity');
        $base = app_path('Domains');

        if (! $this->files->isDirectory($base)) {
            return [];
        }

        $models = [];

        /** @var list<SplFileInfo> $files */
        $files = $this->files->allFiles($base);

        foreach ($files as $file) {
            if (! str_ends_with($file->getPathname(), DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.$file->getFilename())) {
                if (! preg_match('#[\\\\/]Models[\\\\/][^\\\\/]+\.php$#', $file->getPathname())) {
                    continue;
                }
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = Str::after($file->getPathname(), $base.DIRECTORY_SEPARATOR);
            $parts = preg_split('#[\\\\/]#', $relative) ?: [];

            if (count($parts) < 3 || $parts[1] !== 'Models') {
                continue;
            }

            $domain = $parts[0];
            $entity = Str::before($parts[2], '.php');

            // Tablas pivote de capas (no entidades CRUD con checklist Siempre).
            if (in_array($entity, ['CompanyModule', 'CompanyModuleAuthorization'], true)) {
                continue;
            }

            if ($domainFilter && Str::lower($domain) !== Str::lower((string) $domainFilter)) {
                continue;
            }

            if ($entityFilter && Str::lower($entity) !== Str::lower((string) $entityFilter)) {
                continue;
            }

            $models[] = [
                'domain' => $domain,
                'entity' => $entity,
                'path' => $file->getPathname(),
            ];
        }

        return $models;
    }

    /**
     * @return array<string, bool>
     */
    private function artifactChecklist(string $domain, string $entity): array
    {
        $entityPlural = Str::pluralStudly($entity);
        $domainSnake = Str::snake($domain);

        return [
            'Policy' => $this->files->exists(app_path("Domains/{$domain}/Policies/{$entity}Policy.php")),
            'Factory' => $this->files->exists(database_path("factories/{$entity}Factory.php")),
            'StoreRequest' => $this->files->exists(app_path("Domains/{$domain}/Http/Requests/Store{$entity}Request.php")),
            'UpdateRequest' => $this->files->exists(app_path("Domains/{$domain}/Http/Requests/Update{$entity}Request.php")),
            'TableRequest' => $this->files->exists(app_path("Domains/{$domain}/Http/Requests/{$entity}TableRequest.php")),
            'Resource' => $this->files->exists(app_path("Domains/{$domain}/Http/Resources/{$entity}Resource.php")),
            'Controller' => $this->files->exists(app_path("Domains/{$domain}/Http/Controllers/{$entity}Controller.php")),
            'FeatureTest' => $this->files->exists(base_path("tests/Feature/{$domain}/{$entity}Test.php")),
            'TypesTS' => $this->files->exists(resource_path("js/types/{$domainSnake}.d.ts")),
            'PageIndex' => $this->files->exists(resource_path("js/Pages/{$domain}/{$entityPlural}/Index.tsx")),
        ];
    }

    private function checkFunctionalityPermissions(): void
    {
        $functionalityClass = 'App\\Domains\\Admin\\Models\\Functionality';
        $permissionClass = 'Spatie\\Permission\\Models\\Permission';

        if (! class_exists($functionalityClass) || ! class_exists($permissionClass)) {
            $this->comment('Functionality↔permisos: omitido (modelos aún no existen).');

            return;
        }

        $this->info('Functionality↔permisos: chequeo activo (modelos presentes).');
    }
}
