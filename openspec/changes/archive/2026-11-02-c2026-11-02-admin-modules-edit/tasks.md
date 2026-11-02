# Tasks

> Scope: Edit Module (tabs + form) + Volver fijo + quitar menú Funcionalidades.
> CRUD Functionalities anidado: fuera. E2E §4 / R-TEN-05 nuevo / V-* nueva entidad: N/A.

## 1. Shell — backHref fijo

- [x] 1.1 Añadir prop opcional `backHref` a `AppLayout` → `ViewActionBar` (prioridad
  sobre `resolveBackHref`). Verificar: página de prueba o Edit con `backHref` fijo
  muestra Volver a esa URL sin origen en sessionStorage.
- [x] 1.2 Ajustar/añadir test o aserción de contrato si existe cobertura de ActionBar;
  si no, verificación manual documentada en 1.1. Verificar: comportamiento
  «sin origen + sin backHref ⇒ sin Volver» intacto.

## 2. Backend — Update Module

- [x] 2.1 Actualizar `UpdateModuleRequest`: name unique CI ignore self; label/
  explanation como Create (principal required); color/icon/status; **sin** level ni
  slug. Verificar: tests 2.3.
- [x] 2.2 Confirmar `UpdateModule` no toca slug al cambiar name (ya unset). Verificar:
  test update name no altera slug.
- [x] 2.3 Feature tests: update OK; name CI duplicate fail; slug estable tras rename;
  level no cambia si se omite; 403 sin permiso. Verificar:
  `php artisan test --compact` filtrado a Module update verde.
- [x] 2.4 Pint PHP: `vendor/bin/pint --dirty --format agent`. Verificar: exit 0.

## 3. Frontend — Edit Module

- [x] 3.1 Rehacer `Admin/Modules/Edit` con tabs Módulo | Funcionalidades (estructura;
  empty state i18n en Funcionalidades). Verificar: ambas pestañas visibles; sin CRUD.
- [x] 3.2 Form tab Módulo: name, IconPicker, ColorPicker, LangTabs, Switch status;
  sin slug ni level; PUT update; `backHref="/admin/modules"` + showBack. Verificar:
  UI sin level/slug; Volver → Index.
- [x] 3.3 i18n claves nuevas (tabs, empty state) en es/en/ca orden alfabético.
  Verificar: claves presentes en los tres JSON.
- [x] 3.4 Ficha `RecordSummary` (spec `shell-record-summary`) entre ActionBar y tabs:
  título i18n `edicion_modulo` con el nombre, y campos creado, autor y actualizado.
  Verificar: props `created_at` / `updated_at` / `created_by_name` en Edit.

## 4. Menú — quitar Funcionalidades genéricas

- [x] 4.1 Quitar `functionalities` de `CONFIGURATION_SLUGS` / mapa de rutas en
  `MenuBuilder`; ajustar seeder (no exponer Functionality de menú genérica /
  Inactive). Verificar: menú SA sin ítem Funcionalidades.
- [x] 4.2 Actualizar tests de menú (`MenuCorePillarsTest` u otros) que esperen
  `functionalities`. Verificar: esos tests verdes.
- [x] 4.3 Retirar o desactivar acceso de menú a Index/Create/Edit genéricos de
  Functionalities (rutas sin enlace; mínimo según design D5). Verificar: no hay
  entrada de menú; deep link no es requisito de producto en este change.

## 5. Cierre

- [x] 5.1 Smoke: Edit → guardar → slug/level intactos; Volver a Index; menú sin
  Funcionalidades. Verificar: manual o tests anteriores en verde.
- [x] 5.2 Pint final si hubo más PHP. Verificar: exit 0.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → (E2E N/A) → 30 / archive.
- Siguiente change: CRUD Functionalities bajo pestaña Module/Edit.
