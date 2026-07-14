# Tooling de guardrails — generador, tests de arquitectura y CI

> Especificación normativa de las herramientas que hacen cumplir los estándares
> mecánicamente. **Estado: especificado; se implementa como primer change de la Fase 0**
> (requiere instalar el canon de librerías de [libraries.md](libraries.md)).

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
    {--no-frontend : solo backend}
```

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

Además **imprime la checklist restante** que no puede automatizar (rutas al fichero del
dominio, entrada de menú, permisos al seeder, ficha del módulo, vinculaciones §6) para
copiarla a las tasks de OpenSpec.

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
- **Smoke de policies**: toda ruta registrada bajo `auth` responde 403 a un usuario sin
  permisos (recorre la tabla de rutas — caza rutas sin authorize).
- **Smoke de i18n**: las claves usadas en `lang/es.json` existen también en `en.json`.

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
  5. composer audit + npm audit (falla en vulnerabilidad alta)
```

Regla: **rojo = no merge**. Sin excepciones "temporales".

> Nota: el proyecto aún no es repositorio git. Al inicializarlo (Fase 0), primer commit =
> esqueleto + esta documentación; segundo = tooling de este documento.

## 5. Flujo de trabajo con IA (Claude Code)

- `CLAUDE.md` (raíz) carga los estándares como reglas de sesión.
- Skill **`/new-module`**: ejecuta el playbook de forma guiada (ficha → propuesta → checklists).
- Flujo OpenSpec: `/opsx:propose` → revisar → `/opsx:apply` → `/opsx:archive`; el contexto
  de `openspec/config.yaml` apunta a `docs/standards/` para que toda propuesta nazca
  conforme.
- Regla operativa: si durante un desarrollo se detecta que un estándar es incorrecto o
  incompleto, **se para y se corrige el estándar primero** (PR propio), luego se sigue.
