# Proposal

## Why

El ERP necesita el maestro de unidades de v1 (`Unit`) bajo Configuración, como
catálogo global. Sin él no hay base para productos (stock/venta/compra). Se porta
al stack v2 con el mismo patrón que monedas (modales, ServerTable, seeder,
Active-first), dominio Admin.

## What Changes

- Entidad global `Unit` en Admin (sin `company_id`), soft delete, status string
  Active/Inactive + enum PHP.
- Campos: name (unique), slug, symbol max 4. **Sin** `translations`.
- CRUD Configuración: Index ServerTable + Create/Edit modales; Acciones toggle +
  Edit + Delete; sin columna Estado; default Active-first + name.
- Functionality `units` bajo módulo `admin`; permisos `units.*`; menú
  Configuración → Unidades.
- Seeder desde datos v1 con IDs y status preservados; README § Seeders.
- Glosario + ficha Admin; module-map: Unit como catálogo Admin (Catalog consume
  después; sin ProductUnits en este change).

## Capabilities

### New Capabilities

- `admin-units-catalog`: catálogo global Unit, CRUD Configuración / Unidades y
  seeder v1.

### Modified Capabilities

- `menu-core-pillars`: Configuración SA incluye Unidades hacia el Index de units.

## Impact

- Dominio Admin: model, migration, Actions, Policy, FormRequests, controller,
  Resource, routes, páginas Inertia, i18n, MenuBuilder, seeder + README.
- Tests feature (authz, CRUD, orden, seed, cross-company).
- E2E: N/A. Sin deps nuevas. Sin ProductUnits.
