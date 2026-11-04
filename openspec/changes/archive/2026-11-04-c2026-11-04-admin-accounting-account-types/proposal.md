# Proposal

## Why

El ERP necesita el catálogo de **Grupos contables** (PGC España) bajo Configuración
como referencia/asistente para crear cuentas contables más adelante. Se porta el
maestro v1 (`AccountingAccountType`) y su seeder jerárquico al stack v2.

## What Changes

- Entidad global `AccountingAccountType` (sin `company_id`): autoreference, code,
  name, mode nullable, softDeletes; **sin** status Active/Inactive.
- CRUD Configuración: Index ServerTable (code, name, level); Create/Edit modales;
  Edit solo name (code inmutable); Acciones Edit + Delete.
- Vista **show** asistente (jerarquía PGC de referencia).
- Seeder portado de v1 (árbol niveles + i18n `fiscal.*`); README § Seeders.
- Functionality `accounting-account-types`; menú Configuración → Grupos contables.
- Glosario + ficha Admin; module-map (Accounting consumirá después).

## Capabilities

### New Capabilities

- `admin-accounting-account-types-catalog`: catálogo PGC Grupos contables, CRUD,
  show asistente y seeder.

### Modified Capabilities

- `menu-core-pillars`: Configuración SA incluye Grupos contables.

## Impact

- Dominio Admin: model, migration, Actions, Policy, FormRequests, controller,
  Resource, routes, Pages Index/Show, i18n fiscal, MenuBuilder, seeder.
- Tests feature (authz, CRUD, code inmutable, seed smoke, cross-company, show).
- E2E: N/A. Sin endpoint `select` cascada. Sin AccountingAccount.
