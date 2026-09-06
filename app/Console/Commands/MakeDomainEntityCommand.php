<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeDomainEntityCommand extends Command
{
    protected $signature = 'make:domain-entity
                            {domain : Dominio (p.ej. Partners)}
                            {entity : Entidad (p.ej. Worksite)}
                            {--status : enum de estado y transiciones}
                            {--numbered : documento numerado (NumberingService)}
                            {--translatable : HasTranslations + LangTabs}
                            {--lines : documento con líneas}
                            {--seed : Seeder + registro en DatabaseSeeder}
                            {--notifiable : Jobs/Notifications esqueleto}
                            {--exportable : PDF/Excel esqueleto}
                            {--global : catálogo sin tenancy (R-TEN-01 excepción)}
                            {--no-frontend : solo backend}
                            {--force : sobreescribir existentes}';

    protected $description = 'Genera los artefactos Siempre de una entidad de dominio (tooling.md §1)';

    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $domain = Str::studly($this->argument('domain'));
        $entity = Str::studly($this->argument('entity'));
        $force = (bool) $this->option('force');
        $noFrontend = (bool) $this->option('no-frontend');

        $replacements = $this->replacements($domain, $entity);
        $paths = $this->targetPaths($domain, $entity, $noFrontend);

        if ($this->option('status')) {
            $paths['enum'] = app_path("Domains/{$domain}/Enums/{$entity}Status.php");
        }

        $skipped = [];
        $written = [];

        foreach ($paths as $stubKey => $target) {
            if ($this->files->exists($target) && ! $force) {
                $skipped[] = $target;

                continue;
            }

            $stub = $this->stubContents($stubKey, $replacements);

            if ($stub === null) {
                $this->warn("Stub missing for [{$stubKey}], skipped.");

                continue;
            }

            $this->files->ensureDirectoryExists(dirname($target));
            $this->files->put($target, $stub);
            $written[] = $target;
        }

        foreach ($written as $path) {
            $this->line("  <info>created</info> {$this->relative($path)}");
        }

        foreach ($skipped as $path) {
            $this->line("  <comment>skipped</comment> {$this->relative($path)} (exists; use --force)");
        }

        $this->provisionFunctionality($domain, $entity);
        $this->printChecklist($domain, $entity);

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function replacements(string $domain, string $entity): array
    {
        $entityPlural = Str::pluralStudly($entity);
        $domainSnake = Str::snake($domain);
        $entitySnake = Str::snake($entity);
        $table = Str::snake(Str::pluralStudly($entity));
        $permission = Str::snake(Str::pluralStudly($entity));

        return [
            '{{ domain }}' => $domain,
            '{{ entity }}' => $entity,
            '{{ entityPlural }}' => $entityPlural,
            '{{ domainSnake }}' => $domainSnake,
            '{{ entitySnake }}' => $entitySnake,
            '{{ table }}' => $table,
            '{{ permission }}' => $permission,
            '{{ domainLower }}' => Str::lower($domain),
            '{{ entityLower }}' => Str::lower($entity),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function targetPaths(string $domain, string $entity, bool $noFrontend): array
    {
        $entityPlural = Str::pluralStudly($entity);
        $table = Str::snake($entityPlural);
        $timestamp = now()->format('Y_m_d_His');

        $paths = [
            'model' => app_path("Domains/{$domain}/Models/{$entity}.php"),
            'policy' => app_path("Domains/{$domain}/Policies/{$entity}Policy.php"),
            'store_request' => app_path("Domains/{$domain}/Http/Requests/Store{$entity}Request.php"),
            'update_request' => app_path("Domains/{$domain}/Http/Requests/Update{$entity}Request.php"),
            'table_request' => app_path("Domains/{$domain}/Http/Requests/{$entity}TableRequest.php"),
            'resource' => app_path("Domains/{$domain}/Http/Resources/{$entity}Resource.php"),
            'controller' => app_path("Domains/{$domain}/Http/Controllers/{$entity}Controller.php"),
            'create_action' => app_path("Domains/{$domain}/Actions/Create{$entity}.php"),
            'update_action' => app_path("Domains/{$domain}/Actions/Update{$entity}.php"),
            'delete_action' => app_path("Domains/{$domain}/Actions/Delete{$entity}.php"),
            'factory' => database_path("factories/{$entity}Factory.php"),
            'migration' => database_path("migrations/{$timestamp}_create_{$table}_table.php"),
            'feature_test' => base_path("tests/Feature/{$domain}/{$entity}Test.php"),
            'types' => resource_path('js/types/'.Str::snake($domain).'.d.ts'),
            'lang_es' => lang_path('es.json'),
            'lang_en' => lang_path('en.json'),
        ];

        if ($this->option('status')) {
            $paths['enum'] = app_path("Domains/{$domain}/Enums/{$entity}Status.php");
        }

        if (! $noFrontend) {
            $paths['page_index'] = resource_path("js/Pages/{$domain}/{$entityPlural}/Index.tsx");
            $paths['page_create'] = resource_path("js/Pages/{$domain}/{$entityPlural}/Create.tsx");
            $paths['page_edit'] = resource_path("js/Pages/{$domain}/{$entityPlural}/Edit.tsx");
            $paths['page_form'] = resource_path("js/Pages/{$domain}/{$entityPlural}/components/{$entity}Form.tsx");
        }

        return $paths;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function stubContents(string $key, array $replacements): ?string
    {
        $map = [
            'model' => 'model.stub',
            'policy' => 'policy.stub',
            'store_request' => 'store-request.stub',
            'update_request' => 'update-request.stub',
            'table_request' => 'table-request.stub',
            'resource' => 'resource.stub',
            'controller' => 'controller.stub',
            'create_action' => 'create-action.stub',
            'update_action' => 'update-action.stub',
            'delete_action' => 'delete-action.stub',
            'factory' => 'factory.stub',
            'migration' => 'migration.stub',
            'feature_test' => 'feature-test.stub',
            'enum' => 'enum-status.stub',
            'types' => 'types.stub',
            'page_index' => 'page-index.stub',
            'page_create' => 'page-create.stub',
            'page_edit' => 'page-edit.stub',
            'page_form' => 'page-form.stub',
            'lang_es' => 'lang-es.stub',
            'lang_en' => 'lang-en.stub',
        ];

        if (! isset($map[$key])) {
            return null;
        }

        $filename = $map[$key];
        $globalKeys = ['model', 'migration', 'policy', 'factory', 'feature_test'];

        if ($this->option('global') && in_array($key, $globalKeys, true)) {
            $globalName = str_replace('.stub', '.global.stub', $filename);
            if ($this->files->exists(base_path('stubs/domain-entity/'.$globalName))) {
                $filename = $globalName;
            }
        }

        $path = base_path('stubs/domain-entity/'.$filename);

        if (! $this->files->exists($path)) {
            return null;
        }

        $contents = $this->files->get($path);

        return str_replace(array_keys($replacements), array_values($replacements), $contents);
    }

    private function provisionFunctionality(string $domain, string $entity): void
    {
        if ($domain === 'Admin' && in_array($entity, ['Module', 'Functionality'], true)) {
            $this->comment('Skipping Functionality auto-provision for Admin catalog bootstrap.');

            return;
        }

        $functionalityClass = 'App\\Domains\\Admin\\Models\\Functionality';
        $permissionClass = 'Spatie\\Permission\\Models\\Permission';

        if (! class_exists($functionalityClass) || ! class_exists($permissionClass)) {
            $this->warn('Functionality/Permission classes not found — skipping auto-provision (R-AUT-07).');

            return;
        }

        if (! Schema::hasTable('functionalities')) {
            $this->warn('Table functionalities missing — skipping auto-provision (R-AUT-07).');

            return;
        }

        $slug = Str::snake(Str::pluralStudly($entity));

        $functionalityClass::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $entity, 'label' => ['es' => $entity, 'en' => $entity], 'status' => 'active'],
        );

        foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
            $permissionClass::findOrCreate("{$slug}.{$ability}");
        }

        $this->info("Provisioned Functionality [{$slug}] + 5 permissions.");
    }

    private function printChecklist(string $domain, string $entity): void
    {
        $this->newLine();
        $this->info('Checklist restante (no automatizable):');
        $this->line('  - Registrar rutas en routes/domains/'.Str::snake($domain).'.php');
        $this->line('  - Actualizar ficha docs/modules/'.Str::snake($domain).'.md');
        $this->line('  - Responder vinculaciones V-01..V-20 (module-playbook §6)');
        $this->line('  - Registrar Policy en AuthServiceProvider / discovery');
    }

    private function relative(string $path): string
    {
        return Str::after($path, base_path().DIRECTORY_SEPARATOR);
    }
}
