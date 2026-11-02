# Proposal

## Why

El catálogo Spatie de permisos debe poder existir y gestionarse **antes** de la
asignación a Roles. Hoy el ítem de menú Permisos apunta temporalmente a Roles y el
spec prohíbe un CRUD custom; hace falta un Index Admin (como v1 `/admin/permissions`)
que liste, cree custom anclados a Functionality y borre solo lo no canónico, conviviendo
con el auto-provision ya existente en `CreateFunctionality`.

## What Changes

- Nuevo CRUD Admin de **catálogo** de permisos Spatie: Index (`ServerTable`) + Create
  (modal) + Delete; sin Edit de name; sin asignación a Roles/Users.
- Create custom: name `{functionality.slug}.{sufijo}` único; sufijos reservados
  `viewAny|view|create|update|delete|access` rechazados.
- Delete solo custom; canónicos `{slug}.viewAny|…|delete` y `*.access` protegidos.
- Menú Configuración → Permisos → Index real (deja de ir a Roles).
- Autorización alineada a Modules/Roles: Functionality `permissions` (ya en seeder) +
  Policy + Spatie `permissions.*`.
- Hard delete Spatie (sin soft delete del modelo Permission).
- **BREAKING** (comportamiento de menú): el ítem Permisos ya no abre Roles.
- Specs: sustituir el requisito temporal Permisos→Roles y alinear “×7” → **5 + access**.

## Capabilities

### New Capabilities
- `admin-permission-catalog`: CRUD Admin del catálogo Spatie Permission (listado,
  alta custom vinculada a Functionality, borrado de custom; canónicos protegidos;
  convivencia con auto-provision de Functionality).

### Modified Capabilities
- `menu-core-pillars`: ítem Permisos deja de ser temporal hacia Roles; se retira el
  *SHALL NOT* de CRUD custom; contrato de acciones canónicas = 5 + `{module}.access`.

## Impact

- Dominio Admin: controllers/Actions/Requests/Policy, rutas `admin/permissions`,
  páginas Inertia `Admin/Permissions/*`, i18n, `MenuBuilder` (ruta `permissions`).
- Spatie `permissions` / pivots `role_has_permissions` al hard-delete custom.
- Seeder: Functionality `permissions` ya existe; asegurar permisos Spatie y menú.
- Ficha `docs/modules/Admin.md`: CRUD permisos deja de ser “futuro”.
- Sin cambio de module-map ni dependencias Composer/npm.
- E2E Pest browser: no aplica (`e2e.md` §4; cobertura feature tests).
