# Tasks

## 1. Datos / catálogo (sin migración Spatie)

- [x] 1.1 Confirmar Functionality `permissions` (módulo `admin`) activa en seeder y que
      `CreateFunctionality` / seed deja Spatie `permissions.viewAny|view|create|update|delete`
      (+ `admin.access`); verificar con test o assertion de seeder existente
- [x] 1.2 Añadir helper de dominio (p. ej. `CanonicalPermission`) que clasifique name
      canónico (sufijos 5 acciones o `*.access`) vs custom; cubrir con unit/feature corto
- [x] 1.3 R-TEN-05: **N/A** — Permission es catálogo global Spatie (sin `company_id`);
      documentar en test o comentario de suite Admin Permissions (mismo criterio que
      Module/Functionality globales)

## 2. Backend Permission catalog

- [x] 2.1 Registrar `PermissionPolicy` (Spatie Permission o modelo wrapper) con
      `viewAny/view/create/update/delete` → Spatie `permissions.*`; verificar Gate/Policy
      en feature
- [x] 2.2 Actions `CreatePermission` / `DeletePermission` (transaccionales; Create arma
      `{slug}.{Str::slug(sufijo)}`; Delete aborta si canónico); FormRequests Store +
      authorize; verificar rules reserved/unique
- [x] 2.3 `PermissionController`: Index Inertia, `table` ServerTable (name, functionality
      label, flags `can_delete`), store, destroy; rutas `admin/permissions` (+ table);
      feature: 403 sin permiso, Index 200 con `permissions.viewAny`
- [x] 2.4 Feature Create: alta válida; sufijo reservado 422; duplicado 422
- [x] 2.5 Feature Delete: custom 200/redirect y desaparece; canónico / `*.access` 403 y
      permanece

## 3. Frontend + i18n + menú

- [x] 3.1 Página `Admin/Permissions/Index` con ServerTable + ActionBar Nuevo + modal
      Create (Functionality select + sufijo) + Delete confirm solo si `can_delete`;
      verificar props tipadas / smoke render
- [x] 3.2 Claves i18n es/en/ca (títulos, columnas, modal, errores reserved/unique,
      confirm delete); cero literales sueltos en la página
- [x] 3.3 `MenuBuilder`: slug `permissions` → `admin.permissions.index` (ya no Roles);
      feature menú SA: Permisos apunta al Index de permisos

## 4. Vinculaciones y ficha

- [x] 4.1 V-07: permisos `permissions.*` cubiertos por Functionality existente (no
      inventar Functionality nueva); sync/seed verificado en 1.1
- [x] 4.2 V-08: menú Permisos → catálogo (task 3.3); V-09 N/A (reutiliza módulo `admin`)
- [x] 4.3 Actualizar `docs/modules/Admin.md`: CRUD permisos Index+Create+Delete custom,
      canónicos protegidos, menú real; quitar “CRUD permisos = futuro” / Permisos→Roles
- [x] 4.4 Glossary: si falta término Permission/permiso Spatie como catálogo Admin,
      añadirlo; si ya cubierto por Functionality/Module, no inventar fila
- [x] 4.5 E2E rol 40: **N/A** (`e2e.md` §4); declarado en design

## 5. Calidad

- [x] 5.1 Pint + PHPStan limpios en ficheros tocados; suite feature Admin Permissions
      verde en el change

## Workflow follow-up

- Revisar propuesta (Luis) → `/opsx-apply` (20) → 25 → 30 `/opsx-archive` (sin 40).
- Tras archive, el REMOVED/ADDED de `menu-core-pillars` queda en el spec main (Purpose
  del main ya actualizado en propose).
