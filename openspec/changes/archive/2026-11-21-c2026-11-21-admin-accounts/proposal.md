# Proposal

## Why

El SaaS necesita un catálogo de **cuentas** (plantillas de duración y
coste) que anclarán la relación empresa↔ERP. En v1 vive como `Account`
bajo Configuración. En v2 aún no existe; el Module menú `cuenta` es otra
cosa (Mi cuenta / Mis módulos). Este change crea el catálogo + default +
máscara de renovación; `company_accounts` y el 403 real van después.

## What Changes

- Modelo global `Account` (sin `BelongsToCompany`): name, slug,
  description, rate, duration (días), status Active/Inactive,
  `is_default`, soft delete.
- Functionality `accounts` bajo `configuracion`; menú Configuración →
  Cuentas; permisos `accounts.*`; Policy; ServerTable + modales CRUD
  (patrón Unidades).
- Exactamente una Account default (Action al marcar).
- Máscara placeholder de renovación (ComingSoon-like; sin
  `company_accounts` ni bloqueo por caducidad).
- Glosario, ficha Admin, RealignMenuCatalog, seeder mínimo con default.
- Documentar (solo design) enums futuros Pending/Active/Expired/Cancelled
  para `company_accounts` — **sin** crear la tabla aún.

## Capabilities

### New Capabilities

- `admin-accounts-catalog`: catálogo Account + default + máscara renovación.

### Modified Capabilities

_(ninguna obligatoria; menú vía Realign)_

## Impact

- Admin Domain: model/migration/factory/policy/controller/requests/
  resources/pages, seeder, i18n, menu.
- Tests Feature CRUD + authz + V-21; R-TEN-05 N/A (catálogo global).
- E2E: N/A salvo que 10/40 digan lo contrario (flujo catálogo no §4 e2e).
