Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-05-menu-modules-realign
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Contrato: `openspec/changes/c2026-11-05-menu-modules-realign/`
(proposal, design D1/D3/D5, specs menu-visibility + menu-core-pillars, tasks
§3.4 / §6.1–6.2).
Si este prompt y los artefactos chocan, mandan los artefactos.
No modifiques proposal.md, design.md ni los deltas de specs/ salvo un rename
trivial de título de escenario si lo tocas al paso (opcional LOW).
No archives. No escribas tests/Browser/ (E2E N/A en design). No amplíes
alcance: no sembrar Optional del PDF, no campo `sort`, no landings nuevas.

## Problema (HIGH)

El núcleo de producción del change está bien (MenuBuilder data-driven, AND en
menú + `CompanyModuleActivationChecker`, `RealignMenuCatalog`, middleware
`module:configuracion|cuenta|usuarios|empresas`, cache, standards AND). El
subset menú/shell pasa (44 tests).

Fallan el **barrido Feature** (task 6.1 marcada hecha sin evidencia) y el
**shell UI** aún anclado a slugs retirados `admin` / `workspace`.

Efectos observados en Pest (filter amplio Admin/Partners): ~20 fallos Feature
por 403 o asserts a Modules inexistentes. Ejemplos:

| Fallo | Causa |
|---|---|
| `ServerTableSmokeTest` assert `slug=admin` | Module retirado; debe assert `configuracion` (u otro PDF) |
| `ModuleTableReferenceTest` shell `workspace` `firstOrFail` | Module retirado |
| `ModuleTableReferenceTest` Index fuente | exige `row.slug === 'configuracion'`; Index aún tiene `admin`\|`workspace` |
| `CountryTest` / `CurrencyTest` / `UnitTest` / `VatTypeTest` / `AccountingAccountTypeTest` / `GeographyCatalogTest` / `BankTest` «usuario con *.viewAny abre Index» | solo `viewAny` → 403 legítimo (AND HTTP exige `{module}.access`) |
| `CompanyCustomRolesTest` (CRUD/matriz/V-21) | fixture Module `workspace` + capas; rutas ahora `module:usuarios` |
| `CompanyTest` CRUD SA companies | habilita `configuracion`; ruta `module:empresas` |
| `PlatformConsoleTest` (parcial) | revisar comodín/capas tras AND |

Backend `DeleteModule::SHELL_SLUGS` ya es
`dashboard|cuenta|usuarios|empresas|configuracion`. Frontend Index/Edit no.

## Qué hacer

### 1. Shell UI Modules (obligatorio HIGH)

Alinear detección de shell con `DeleteModule` (misma lista PDF):

- `resources/js/Pages/Admin/Modules/Index.tsx` — `isShell`
- `resources/js/Pages/Admin/Modules/Edit.tsx` — `isShellModule`

Sustituir `admin` \| `workspace` por los cinco slugs PDF (o helper/const compartido
si el repo ya tiene patrón; no inventar módulo TS nuevo salvo reutilizar algo
existente). Objetivo: no ofrecer delete en UI para esos slugs; el test de
referencia que busca `configuracion` en el fuente debe pasar.

### 2. Barrido Feature (obligatorio HIGH — task 6.1 / 6.2)

Actualizar tests al contrato AND + mapa D1/D5. No debilitar AND (no volver a
aceptar solo `viewAny` en HTTP).

**Patrón happy-path Index (catálogos bajo `configuracion`):**

```php
Permission::findOrCreate('configuracion.access');
$user->givePermissionTo(['configuracion.access', '{functionality}.viewAny']);
// capas: enableModuleForCompany($company, 'configuracion') si aplica
```

Mantener/asegurar un caso explícito «solo viewAny sin comodín → 403» (ya hay
modelo en `PermissionCatalogTest`).

**Fixtures de middleware:**

- Rutas companies / partners companies → Module `empresas` autorizado+encendido
  (SA Basic sigue necesitando capas; no basta `configuracion`).
- Rutas company-roles / members → Module `usuarios` (+ comodín `usuarios.access`
  para no-SA).
- Rutas company-modules → Module `cuenta`.
- Sustituir fixtures que crean/encienden Module slug `workspace` o `admin` por
  los slugs PDF correspondientes (`CompanyCustomRolesTest`, etc.).

**Asserts residuales:**

- `ServerTableSmokeTest`: no exigir `admin`; exigir p. ej. `configuracion`
  (y/o ausencia de `admin`/`workspace`).
- `ModuleTableReferenceTest` «desactivar/reactivar shell»: usar Modules PDF
  existentes tras seeder (`configuracion` + al menos un Basic shell, p. ej.
  `dashboard` o `empresas`), no `workspace`.
- Grep Feature: `slug', 'admin'`, `slug', 'workspace'`, `module:admin`,
  `enableModuleForCompany(..., 'admin'|'workspace')` → 0 pendientes de contrato
  viejo (salvo variables locales renombradas que no sean Module slug, p. ej.
  `$workspace` como Company de sesión: renombrar si confunde, no obligatorio).

### 3. Docs menores (LOW, mismo pase si tocas)

- `docs/modules/Admin.md`: frase «workspace» en company-roles → wording PDF
  (`usuarios`).
- Opcional: título escenario delta «SA no recibe 403 module:admin» →
  `module:configuracion` (el cuerpo ya está bien; main spec ya limpio).

### 4. No tocar (ya OK según 25)

- `MenuBuilder`, `MenuCatalogCache`, `RealignMenuCatalog`, migración realign,
  `CompanyModuleActivationChecker` AND, `SyncManagerPermissionsForCompany`,
  standards kernel/architecture/`01-kernel.mdc`, rutas `module:` ya migradas,
  AsideNav hoja `href`.
- No reabrir diseño OR; no sembrar Optional del PDF.

## Gate

PHP Laragon (PATH sin `php`):

`C:\laragon\bin\php\php-8.4.27-1-Win32-vs17-x64\php.exe`

Ejecutar al menos:

```text
artisan test --compact ^
  tests/Feature/Admin/MenuVisibilityTest.php ^
  tests/Feature/Admin/MenuCorePillarsTest.php ^
  tests/Feature/Admin/ShellNavigationTest.php ^
  tests/Feature/Admin/CompanyModuleLayersTest.php ^
  tests/Feature/Admin/PermissionCatalogTest.php ^
  tests/Feature/Admin/ServerTableSmokeTest.php ^
  tests/Feature/Admin/ModuleTableReferenceTest.php ^
  tests/Feature/Admin/CompanyCustomRolesTest.php ^
  tests/Feature/Admin/CountryTest.php ^
  tests/Feature/Admin/CurrencyTest.php ^
  tests/Feature/Admin/UnitTest.php ^
  tests/Feature/Admin/VatTypeTest.php ^
  tests/Feature/Admin/AccountingAccountTypeTest.php ^
  tests/Feature/Admin/GeographyCatalogTest.php ^
  tests/Feature/Banks/BankTest.php ^
  tests/Feature/Partners/CompanyTest.php ^
  tests/Feature/Admin/PlatformConsoleTest.php
```

Ideal: filter residual `admin|workspace` en Feature sin fallos de contrato viejo.

- `vendor/bin/pint --dirty --format agent` si hay PHP tocado.
- PHPStan paths tocados si aplica.
- No archives; devolver a **25-architecture-review** para re-auditoría.
