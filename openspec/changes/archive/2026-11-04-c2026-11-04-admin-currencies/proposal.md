# Proposal

## Why

El ERP necesita el maestro de monedas de v1 (`Currency`) bajo Configuración, como
catálogo global. Sin él no hay base para settings de empresa, facturas ni cobros.
Se porta la lógica y los datos de v1 al stack v2 (Inertia, ServerTable, Policies),
mismo patrón que countries.

## What Changes

- Entidad global `Currency` (sin `company_id`), soft delete, status string
  Active/Inactive + enum PHP.
- Campos alineados a v1: `name`, `slug`, `code`, `number`, `symbol` (**max 8**,
  no char(2) de v1).
- CRUD Admin plano: Index ServerTable (name, code, number, symbol; Acciones:
  toggle + Edit modal + Delete); Create/Edit por modal; **sin columna Estado**.
- Edit: **code y number inmutables** (como v1); editables name→slug y symbol.
- Functionality `currencies` bajo módulo `admin`; permisos `currencies.*`; menú
  Configuración → Monedas.
- Seeder desde datos v1 con **IDs preservados**; Active solo habituales
  (EUR/USD/GBP/CHF); resto Inactive. Sin import automático al abrir Index.
- Documentar seeder en `README.md` § Seeders.
- Glosario + ficha Admin actualizados.

## Capabilities

### New Capabilities

- `admin-currency-catalog`: catálogo global Currency, CRUD Configuración / Monedas
  y seeder v1.

### Modified Capabilities

- `menu-core-pillars`: Configuración SA incluye Monedas hacia el Index de
  currencies.

## Impact

- Dominio Admin: model, migration, Actions, Policy, FormRequests, controller,
  Resource, routes, página Inertia `Admin/Currencies`, i18n, `MenuBuilder` +
  Functionality en AdminCatalog, seeder datos (JSON/upsert por id).
- Tests feature (authz, CRUD, immutables, seed smoke, visibilidad cross-company).
- E2E: N/A (`e2e.md` §4).
- Sin module-map nuevo (Admin ya existe); sin dependencias Composer/npm nuevas.
