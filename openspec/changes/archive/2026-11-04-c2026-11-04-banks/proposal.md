# Proposal

## Why

El ERP necesita el maestro de bancos de v1 (`Bank`) bajo Configuración, como
catálogo global. Sin él no hay base para cuentas, remesas ni cobros. Se porta al
stack v2 (Inertia, ServerTable, Policies) con el mismo patrón operativo que
monedas, en un dominio de código **Banks** (no Admin ni Treasury).

## What Changes

- Dominio nuevo **Banks** (`app/Domains/Banks`); entidad global `Bank` (sin
  `company_id`), soft delete, status string Active/Inactive + enum PHP.
- Campos v1: name, slug, tradename, lei, eu_code, supervisor_code, swift,
  swift_source, swift_updated_at.
- CRUD Configuración: Index ServerTable + Create/Edit **modales**; Acciones
  toggle + Edit + Delete; sin columna Estado; default Active-first + name.
- Functionality `banks` bajo módulo SaaS `admin`; permisos `banks.*`; menú
  Configuración → Bancos.
- Seeder desde `banks.enriched.json` v1; **IDs y status preservados**; documentado
  en README § Seeders. Sin import al abrir Index.
- Actualizar module-map (Bank sale de Treasury → Banks), glosario y ficha del
  dominio/módulo.

## Capabilities

### New Capabilities

- `banks-catalog`: catálogo global Bank (dominio Banks), CRUD Configuración /
  Bancos y seeder v1.

### Modified Capabilities

- `menu-core-pillars`: Configuración SA incluye Bancos hacia el Index de banks.

## Impact

- Dominio Banks: model, migration, Actions, Policy, FormRequests, controller,
  Resource, routes `admin/banks*`, páginas Inertia, i18n, MenuBuilder +
  Functionality en AdminCatalog, seeder + README.
- module-map.md + docs/modules (ficha Banks) + glossary.
- Tests feature (authz, CRUD, seed, Active-first, cross-company).
- E2E: N/A. Sin deps Composer/npm nuevas. Sin BankAccount.
