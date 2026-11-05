# Tasks

## 1. Standards (kernel AND)

- [x] 1.1 Actualizar `docs/standards/kernel.md` §4–5: Module puede ser hoja de
      menú; permisos Module **AND** Functionality; quitar «Prohibido AND v1» y
      la implicación del comodín sobre todos los `viewAny`. Verificar diff
      textual OR→AND.
- [x] 1.2 Actualizar `docs/standards/architecture.md` R-AUT-08 + anti-patrón
      (§lista) al modelo AND. Verificar coherencia con kernel.
- [x] 1.3 Alinear `.cursor/rules/01-kernel.mdc` (y menciones OR en CLAUDE /
      AGENTS / laravel-boost adapters si citan OR) al AND. Verificar grep
      `Prohibido el AND` / `comodín OR` en docs/rules = 0 pendientes.

## 2. Datos / catálogo

- [x] 2.1 Migración o comando idempotente: crear Modules `dashboard`, `cuenta`,
      `usuarios`, `empresas`, `configuracion` (levels Basic/AdminOnly según
      design D1); reasignar Functionalities (D2); sembrar `profile` bajo
      `cuenta` si falta; retirar `admin`/`workspace`. Verificar en tinker/test
      que no quedan esos slugs y `module_id` correcto.
- [x] 2.2 Migrar permisos comodín Spatie `admin.access`→`configuracion.access`,
      `workspace.access`→comodines Basic; provisionar
      `dashboard|cuenta|usuarios|empresas|configuracion.access`. Verificar
      grants Manager/SA tras sync.
- [x] 2.3 Reescribir `AdminCatalogSeeder` al mapa D1/D2 (autorizar+encender
      Basics en companies demo). Verificar `db:seed` limpio / idempotente.
- [x] 2.4 Actualizar `SyncManagerPermissionsForCompany` (y tests asociados) a
      comodines/funcs nuevos. Verificar Manager ve menú Basic con AND.

## 3. Backend menú + cache + rutas

- [x] 3.1 Implementar cache de catálogo Active + invalidación en Actions CUD
      Module/Functionality. Verificar test: mutar Functionality → menú refleja
      sin stale; dos companies filtran distinto.
- [x] 3.1b D6 payload plano (arrays) en `MenuCatalogCache` — no Eloquent bajo
      `serializable_classes=false` / store database. Verificar
      `MenuCatalogCacheDatabaseTest` (warm+hit + dashboard Inertia).
- [x] 3.2 Remodelar `MenuBuilder`: sin `CONFIGURATION_SLUGS` /
      `FUNCTIONALITY_GROUPS` / bloques sintéticos; raíces desde Modules; AND;
      Dashboard hoja con `href`; excepción SA AdminOnly sobre
      `configuracion`. Verificar `MenuVisibilityTest` / `MenuCorePillarsTest`
      verdes al nuevo contrato.
- [x] 3.3 Actualizar `hrefFor`/labels (`profile`, Mis módulos, etc.) y i18n
      keys faltantes (es/en/ca). Verificar strings en menú.
- [x] 3.4 Sustituir middleware `module:admin`→`module:configuracion` y
      `module:workspace`→`usuarios|empresas|cuenta` según design D5 en
      `routes/domains/*`; preservar escape hatch Modules Index si aplica.
      Verificar `route:list` + tests Shell/CompanyModuleLayers.
- [x] 3.5 Asegurar policies/Gate de Functionalities reubicadas siguen
      exigiendo permiso de acción; documentar que AND de comodín se aplica en
      menú + (si existe checker) acceso HTTP. Verificar escenario 403 solo
      `viewAny` sin comodín (Feature).

## 4. Frontend

- [x] 4.1 Aside/nav: Module con `href` y `functionalities` vacío = enlace
      directo (Dashboard). Verificar UI / snapshot test o Feature Inertia
      props.
- [x] 4.2 Actualizar tipos TS del menú si el shape añade `href` en raíz.
      Verificar `tsc`/lint del front afectado.

## 5. Docs de módulo + registro

- [x] 5.1 Actualizar `docs/modules/Admin.md` (mapa menú, slugs, nota PDF
      backlog). Verificar ficha coherente con design D8.
- [x] 5.2 Actualizar Purpose de `openspec/specs/menu-visibility/spec.md` y
      `menu-core-pillars/spec.md` en main al contrato AND + Modules reales
      (delta no lleva Purpose). Verificar texto Purpose ≥50 chars y alineado.

## 6. Tests (regresión)

- [x] 6.1 Reescribir/actualizar `MenuVisibilityTest`, `MenuCorePillarsTest`,
      `ShellNavigationTest`, `CompanyModuleLayersTest` (y refs
      `module:admin`/`workspace` en Feature). Verificar
      `php artisan test --compact` en esos ficheros en verde.
- [x] 6.2 Añadir casos AND explícitos: solo viewAny → oculto+403; solo comodín
      → oculto; ambos → visible. Verificar Pest nuevos.
- [x] 6.3 Añadir caso Module hoja `dashboard` y ausencia de raíces fantasma.
      Verificar Pest.
- [x] 6.4 R-TEN-05: N/A entidades nuevas de negocio; confirmar aislamiento
      company en menú (company A no ve módulos solo de B) sigue cubierto.
      Verificar test capas existente o ajuste mínimo.
- [x] 6.5 E2E Pest browser: no aplica (declarado en design).

## 7. Calidad

- [x] 7.1 `vendor/bin/pint --dirty` sobre PHP tocado; verificar formato.
- [x] 7.2 `vendor/bin/phpstan analyse` (paths tocados / suite proyecto) sin
      errores nuevos.

## Workflow follow-up

- Revisión humana Luis → `/opsx-apply` (20) → 25-architecture-review →
  30-testing / archive.
- No rol 40 (E2E N/A).
