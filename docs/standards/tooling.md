# Tooling de guardrails — generador, tests de arquitectura y CI

> Especificación normativa de las herramientas que hacen cumplir los estándares
> mecánicamente. **Estado: implementado** (change `kernel-tooling-fase0`, 2026-07-16) salvo
> el flag `--global` del generador (§1), añadido después y pendiente de construir.

## 1. Generador `make:domain-entity`

Comando artisan propio con stubs del proyecto. Genera de una vez **todos** los artefactos
de la checklist del playbook (§3–§5) conformes a los templates
([templates-backend.md](templates-backend.md) / [templates-frontend.md](templates-frontend.md)),
de modo que sea imposible "olvidarse una pieza" al arrancar una entidad.

### Firma

```
php artisan make:domain-entity {domain} {entity}
    {--status : entidad con enum de estado y transiciones}
    {--numbered : documento numerado (integra NumberingService)}
    {--translatable : campos traducibles (HasTranslations + LangTabs)}
    {--lines : documento con líneas (integra LineItemsEditor)}
    {--seed : genera Seeder de datos base/catálogo + registro en DatabaseSeeder}
    {--notifiable : añade Jobs/Notifications esqueleto (company_id explícito, envío por cola)}
    {--exportable : añade generación de PDF/Excel esqueleto (plantilla + Action por cola)}
    {--global : catálogo global sin tenancy — excepción de R-TEN-01 (ver más abajo)}
    {--no-frontend : solo backend}
```

Los flags mapean 1:1 con la columna "condicional" de la
[matriz de artefactos](module-playbook.md#matriz-de-artefactos-por-entidad-referencia-rápida);
lo marcado como "Siempre" en esa matriz se genera sin necesidad de flag.

### El flag `--global` (catálogos sin tenancy)

R-TEN-01 ya preveía una excepción para "catálogos globales" (países, provincias, tipos de
IVA base...) sin mecanismo para producirla — este flag es ese mecanismo. Categoría
recurrente, no un caso aislado: `currencies`, `countries`, `provinces`, `banks`,
`Module`/`Functionality` (R-AUT-07), y las que vayan apareciendo.

**No confundir con relaciones N:M reales** (p. ej. `users` ↔ `companies` vía
`user_companies`, un usuario en varias empresas a la vez): eso no es "sin tenancy", es una
forma relacional distinta y no lo cubre este flag.

Con `--global` activo, el generador omite:
- **Migration**: sin columna/FK/índice `company_id`.
- **Model**: sin trait `BelongsToCompany`.
- **FormRequest**: `unique`/`exists` simples, sin el scoping de tenant de R-TEN-07 (no
  aplica).
- **Policy**: sin `belongsToCurrentCompany()` — autorización puramente por permiso.
- **Factory**: sin `company_id => Company::factory()`.
- **Test de aislamiento de tenant (R-TEN-05)**: no se genera — se sustituye por un test que
  confirma que el dato se ve igual desde cualquier empresa (la inversa de la prueba
  habitual).

### Salida (ejemplo `make:domain-entity Partners Worksite --status`)

```
app/Domains/Partners/Models/Worksite.php
app/Domains/Partners/Enums/WorksiteStatus.php
app/Domains/Partners/Policies/WorksitePolicy.php
app/Domains/Partners/Actions/{CreateWorksite,UpdateWorksite,DeleteWorksite}.php
app/Domains/Partners/Http/Requests/{StoreWorksiteRequest,UpdateWorksiteRequest,WorksiteTableRequest}.php
app/Domains/Partners/Http/Resources/WorksiteResource.php
app/Domains/Partners/Http/Controllers/WorksiteController.php
database/migrations/<ts>_create_worksites_table.php
database/factories/WorksiteFactory.php
tests/Feature/Partners/WorksiteTest.php          # CRUD + permisos + aislamiento tenant
resources/js/Pages/Partners/Worksites/{Index,Create,Edit}.tsx
resources/js/Pages/Partners/Worksites/components/WorksiteForm.tsx
resources/js/types/partners.d.ts                 # merge si ya existe
lang/es.json · lang/en.json                      # claves base de la entidad (merge)
```

Como último paso, el generador **registra la entrada de menú y provisiona los permisos**:
crea (o actualiza) la fila `Functionality` de la entidad (`module_id`, `slug` = el mismo de
sus permisos, `label`) y llama a `Permission::firstOrCreate` para sus 5 habilidades
(`viewAny/view/create/update/delete` — R-AUT-07). Esto es lo que en v1 obligaba a
desplegar las rutas primero y crear la Functionality después a mano; en v2 ambas cosas
nacen en el mismo change. `PermissionSeeder` deja de ser la única vía de creación: solo
siembra el estado inicial (instalación limpia/CI); la creación real vive en la Action de
`Functionality` con `firstOrCreate` (idempotente, igual que hacía v1 en su comprobación
defensiva de permisos "por si faltan").

Además **imprime la checklist restante** que no puede automatizar (rutas al fichero del
dominio, ficha del módulo, vinculaciones §6) para copiarla a las tasks de OpenSpec.

### Reglas

- Los stubs viven en `stubs/domain-entity/` y se mantienen sincronizados con los templates
  de esta documentación (cambio en template ⇒ cambio de stub en el mismo PR).
- El generador nunca sobreescribe ficheros existentes (`--force` explícito).
- Nombres validados contra el [glosario](glossary.md) cuando el término exista (aviso si se
  usa un identificador español no registrado).

## 2. Tests de arquitectura (Pest arch)

`tests/Architecture/` — corren en CI con el resto de la suite. Reglas mínimas iniciales
(se amplían al detectar cada nueva violación repetible):

```php
// Estructura y capas
arch('los modelos viven en su dominio')
    ->expect('App\Domains')->classes()->extending(Model::class)
    ->toBeInDirectories('app/Domains/*/Models');

arch('Support no conoce los dominios')                    // R-ARQ-02
    ->expect('App\Support')->not->toUse('App\Domains');

arch('los Services de un dominio son privados')           // R-DOM-03
    // los Services de X solo se usan desde App\Domains\X (config generada de module-map.md)
    ->expect('App\Domains\*\Services')->toOnlyBeUsedIn('App\Domains\{domain}');

// Tenancy
arch('todo modelo de negocio usa BelongsToCompany')       // R-TEN-02
    ->expect('App\Domains')->classes()->extending(Model::class)
    ->toUseTrait(BelongsToCompany::class)
    ->ignoring([/* excepciones explícitas: catálogos globales */]);

arch('prohibido session(currentCompany)')                  // R-TEN-03
    ->expect(['session'])->not->toBeUsedIn('App\Domains'); // acceso solo vía CurrentCompany

// Capas
arch('controllers delgados: sin acceso a DB directo')      // R-CAP-01
    ->expect('App\Domains\*\Http\Controllers')->not->toUse([DB::class]);

arch('las Actions son invocables con handle()')            // R-CAP-02
    ->expect('App\Domains\*\Actions')->toHaveMethod('handle');

arch('FormRequests en Http/Requests')                       // R-CAP-04
    ->expect('App\Domains')->classes()->extending(FormRequest::class)
    ->toBeInDirectories('app/Domains/*/Http/Requests');

// Higiene general
arch('sin dd/dump/env fuera de config')
    ->expect(['dd', 'dump', 'ray', 'env'])->not->toBeUsedIn('App');
```

Complemento no-arch (feature tests globales):
- **Kernel** (`tests/Architecture/KernelInvariantsTest.php`): sin Livewire, sin
  `session('currentCompany')`, Company/Module/Functionality sin `BelongsToCompany`,
  tres capas presentes, User↔Company, Super Admin en `Gate::before`.
- **Smoke de policies**: toda ruta registrada bajo `auth` responde 403 a un usuario sin
  permisos (recorre la tabla de rutas — caza rutas sin authorize).
- **Smoke de i18n**: las claves usadas en `lang/es.json` existen también en `en.json`.

### Backstop de completitud (detectar huecos)

Ni la disciplina documental ni el generador garantizan por sí solos que una entidad quede
completa: el generador no cubre lo que se escribe a mano después (vinculaciones, ajustes).
Dos mecanismos adicionales, complementarios entre sí:

1. **Test de completitud** (`tests/Architecture/EntityCompletenessTest.php`) — no es un
   `arch()` fluido (Pest arch no expresa "el fichero hermano existe"), es un test
   convencional que recorre `app/Domains/*/Models/*.php` y, para cada modelo, comprueba
   que existan sus artefactos "Siempre" de la
   [matriz de module-playbook.md](module-playbook.md#matriz-de-artefactos-por-entidad-referencia-rápida)
   (Policy, Factory, FormRequests, Resource, Controller, ruta con nombre, tipos TS, test
   feature). Corre en CI con el resto de la suite: si falta algo, el build falla señalando
   el fichero exacto ausente.
2. **Comando `artisan module:audit {domain?} {entity?}`** — el mismo chequeo, bajo demanda
   y con salida legible (tabla en consola: artefacto ✓/✗). Uso principal: paso explícito
   del gate de [30-testing](../agents/30-testing.md) antes de dar una entidad por
   terminada, y ayuda de desarrollo mientras se construye.

Ambos mecanismos incluyen también la integridad `Functionality`↔permisos (R-AUT-07): toda
`Functionality` con `status` activo debe tener sus 5 permisos `viewAny/view/create/update/
delete` presentes en la tabla `permissions`. Es la comprobación mecánica de lo que en v1
resolvía a mano la comprobación defensiva de `FunctionalityController::update()`.

El test de completitud es innegociable (lo hace cumplir CI); el comando es la versión
"para humanos" del mismo chequeo, pensada para ejecutarse antes de llegar a CI.

## 3. Calidad estática

| Herramienta | Config | Regla |
|---|---|---|
| **PHPStan (larastan)** | `phpstan.neon`, **nivel 6** de partida (subir a 8 cuando la base sea estable) | Verde obligatorio en CI |
| **Pint** | preset `laravel` (`pint.json`) | `--test` en CI; formateo local pre-commit |
| **TypeScript** | `tsconfig.json` `strict: true` | `tsc --noEmit` en CI |
| **ESLint + Prettier** | preset react + tailwind | verde en CI |

## 4. Pipeline CI (GitHub Actions — se define al crear el repo git)

```
push/PR →
  1. composer install + npm ci
  2. Pint --test · PHPStan · ESLint · tsc --noEmit
  3. Pest (unit + feature + arch) con MySQL de servicio (strict mode ON — mismo motor que prod, R-BD-01)
  4. npm run build (el frontend compila)
  5. Pest browser (E2E de flujos críticos, job `e2e` — docs/standards/e2e.md)
  6. composer audit + npm audit (falla en vulnerabilidad alta)
```

Regla: **rojo = no merge**. Sin excepciones "temporales".

> Nota: el proyecto aún no es repositorio git. Al inicializarlo (Fase 0), primer commit =
> esqueleto + esta documentación; segundo = tooling de este documento.

## 5. Flujo de trabajo con IA (Claude Code / Cursor / Codex)

- Roles de agente 10-architecture / 20-implementation / 40-e2e / 30-testing: fichas
  canónicas en `docs/agents/`, cargadas por adaptadores finos por herramienta:
  `.cursor/rules/` (Cursor), `.claude/agents/` (Claude Code, con modelo por rol: fable
  en 10, sonnet en 20/30/40) y `AGENTS.md` (Codex y compatibles).
- Invariantes always-on: `docs/standards/kernel.md` → `.cursor/rules/01-kernel.mdc`
  (`alwaysApply`) y `CLAUDE.md`. El harness Cursor versionado es
  `.cursor/{rules,skills,agents,commands}` (el resto de `.cursor/` sigue gitignorado).
- `CLAUDE.md` (raíz) carga los estándares como reglas de sesión; en Cursor lo hace
  `.cursor/rules/00-project.mdc` + `01-kernel.mdc` (alwaysApply).

### MCP (workspace)

`.mcp.json` en la raíz. En Cursor hay que **encender** cada servidor (paleta →
MCP Settings). El `command` de Boost es el `php.exe` de Laragon: el PATH del IDE
no incluye `php`.

| Servidor | Uso |
|---|---|
| `codegraph` | Grafo de **nuestro** código (símbolos, callers, impacto). Regla: `.cursor/rules/codegraph.mdc` |
| `laravel-boost` | Docs Laravel vigentes + runtime local (`Search Docs`, schema, logs). Regla: `.cursor/rules/laravel-boost.mdc`. No sustituye el kernel |

**Boost vs Tabularis:** el esquema/query de **esta** app va por Boost (conexión
Laravel / `.env` local). Tabularis es MCP de **usuario** para SQL ad-hoc u otras
BDs. No añadir aquí la conexión Hetzner / database `back`.

`boost:install --guidelines --skills` es **selectivo**: allowlist Laravel 13,
Inertia React 3, Pest 4, Pint, Tailwind 4. Prohibido publicar Livewire / Flux /
Folio / Herd / Sail / Volt. El installer solo puede rellenar el bloque
`<laravel-boost-guidelines>` de `CLAUDE.md`/`AGENTS.md`; el resto del harness no
se pisa. `boost:update` para refrescar recursos ya publicados.

`Record Rule` / `.ai/rules` no son constitución (kernel y OpenSpec mandan).
`BOOST_RULES_ENABLED=false` en `.env.example` desactiva esa vía.

- Skill **`/new-module`**: ejecuta el playbook de forma guiada (ficha → propuesta → checklists).
- Flujo OpenSpec: `/opsx:propose` → revisar → `/opsx:apply` → `/opsx:archive`; el contexto
  de `openspec/config.yaml` apunta a `docs/standards/` para que toda propuesta nazca
  conforme.
- Regla operativa: si durante un desarrollo se detecta que un estándar es incorrecto o
  incompleto, **se para y se corrige el estándar primero** (PR propio), luego se sigue.
