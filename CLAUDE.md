# ERP v2 (SaaS) — Laravel 13 + Inertia + React

v2 multi-tenant del ERP Viuda Vila. Referencia funcional (v1, solo lectura):
`C:\laragon\www\laravel8_erp_viudavila_github`. Visión y decisiones de producto:
`docs/vision-v2.md`.

## Normativa obligatoria

**`docs/standards/` es normativo para TODO desarrollo.** Antes de:

- cualquier change → cumplir `docs/standards/kernel.md` (User/Company, Module ≠ Functionality, tres capas, Super Admin, sin Livewire)
- crear un módulo o entidad → leer `docs/standards/module-playbook.md` y usar el skill `/new-module`
- escribir código backend → cumplir `docs/standards/architecture.md` (reglas `R-*`)
- escribir UI → `docs/standards/frontend.md` (patrones y catálogo de componentes; no crear componentes que ya existan)
- copiar un patrón → partir de `docs/standards/templates-*.md`, no inventar estructura
- añadir una dependencia → debe estar en `docs/standards/libraries.md` (si no está: justificar y añadirla al canon en el mismo PR)
- nombrar algo del dominio → `docs/standards/glossary.md` (código en inglés; UI en español vía i18n)
- tests de navegador → `docs/standards/e2e.md` (Pest browser; sin sandbox Playwright aparte)

Excepciones a los estándares: solo declaradas por escrito en el design de OpenSpec del cambio.
Si un estándar está mal: corregir el estándar primero, después seguir.

## Reglas capitales (resumen — el detalle manda en standards)

1. Multi-tenant BD única: todo modelo de negocio con `BelongsToCompany` (Company es el tenant; Module/Functionality son catálogo global); jamás `session('currentCompany')`; test de aislamiento cruzado obligatorio por entidad.
2. Sin empresa preferente hardcodeada (esto es un SaaS de N empresas iguales). User puede existir sin empresa; no entra al ERP sin ≥1 company activa.
3. Autorización = Policy por modelo + permisos Spatie `<functionality>.<acción>`. Nada de middleware ad-hoc. Super Admin bypassa permisos/módulos, no el perímetro de companies vinculadas.
4. Module ≠ Functionality. Tres capas: catálogo Active/Inactive + elegibilidad + activación por empresa. Menú = las tres **y** (comodín OR `viewAny`).
5. Controllers delgados → Actions transaccionales → eventos de dominio. Cross-domain: leer libre, escribir SOLO vía Action del dominio propietario o evento (matriz en `module-map.md`).
6. Estados = enums PHP con transiciones sobre columna `string` (jamás `ENUM` de MySQL); numeración solo vía `NumberingService`; decimales 3 venta / 4 coste / 2 contable; MySQL strict ON; nada de `serialize()`.
7. Contabilidad solo por eventos → LedgerGenerator → SeatBuilder → Seat (idempotente, cuadrado).
8. Listados = `<ServerTable>` (Tabulator) contra el contrato `*/table`; formularios = RHF + zod espejo del FormRequest; cero literales sin i18n.
9. Todo texto UI en español (+en/ca) vía i18n; identificadores en inglés según glosario. Front = Inertia + React (**no Livewire**).

## Flujo de trabajo

OpenSpec: `/opsx:propose` → revisar → `/opsx:apply` → `/opsx:archive`. Los cambios de
código nacen de una propuesta; la ficha del módulo (`docs/modules/<módulo>.md`) se crea
antes de proponer (checklist de vinculaciones incluida).

Metodología de roles (transversal Claude/Cursor/Codex): fichas canónicas en `docs/agents/`
— 10-architecture (propose), 20-implementation (apply), 25-architecture-review
(auditoría de solo lectura tras apply), 40-e2e (Pest browser), 30-testing (verificar
DoD + archive). Al ejecutar cada fase, actuar según la ficha del
rol correspondiente. El estado del change vive en los artefactos OpenSpec: si
conversación y artefactos chocan, mandan los artefactos; regla de oro: no inventar
requisitos no especificados.

`/opsx:explore` no es un rol (no implementa, no ejecuta comandos OpenSpec). **Norma fija**:
el flujo de cualquier change es siempre debate en explore → brief con
`docs/prompts/template_prompt.md`, guardado como fichero en `docs/prompts/<Change>.md` →
ejecución con `10-architecture` como **subagente nuevo**, lanzado por el usuario (explore
nunca lo invoca ni continúa en el mismo hilo bajo ese rol). Al redactar el brief, protocolo
estricto de `docs/agents/README.md` §"Redactar el brief de handoff": usar la plantilla tal
cual, ceñirse solo a lo debatido (preguntar ante cualquier duda, cuantas veces haga falta,
nunca inferir) y no ejecutar nada del change mientras se redacta.

## Comandos

- `composer dev` — serve + queue + pail + vite
- `composer test` — suite completa (Pest cuando esté instalado; incluye tests de arquitectura)
- `vendor/bin/pint` · `vendor/bin/phpstan analyse` — antes de dar nada por terminado

## Estado actual

Fase 0 en curso: kernel de tenancy (Company en Partners, User↔Company, tres capas
de módulos, menú endurecido), catálogo Admin, tooling, Pest browser, Laravel Boost
MCP. Harness de agentes: `docs/standards/kernel.md` + `.cursor/rules/01-kernel.mdc` +
`.cursor/rules/laravel-boost.mdc`. UI = Inertia v3 + React 19 (no Livewire).
Tabularis: MCP de usuario, pendiente conexión a la BD de este ERP.

===

<laravel-boost-guidelines>
=== .ai/erp-v2-kernel rules ===

# ERP v2 — Boost no sustituye el kernel

This application is **Inertia v3 + React 19**, not Livewire, not Blade CRUD, not yajra DataTables.

If any Boost guideline or skill contradicts `docs/standards/kernel.md` or architecture `R-*`, **follow the kernel**. Boost is for Laravel/Inertia/Pest/Tailwind **APIs**, not tenancy, Module/Functionality, or menu.

- Use `CurrentCompany`, never `session('currentCompany')`.
- Domain code lives in `app/Domains/<Dominio>/`.
- Listings: `<ServerTable>` (Tabulator).
- Permissions: `<functionality>.<acción>`.
- Do not write business invariants with Boost `Record Rule` (`.ai/rules` is disabled).

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
