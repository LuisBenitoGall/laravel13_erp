# Proposal

## Why

El ERP necesita el maestro de tipos de IVA bajo Configuración. Respecto a v1
(`IvaType` plano), en v2 cada tipo pertenece a un **Country** (obligatorio): el
IVA es fiscal por país. Soft delete preserva el historial comercial.

## What Changes

- Entidad global `VatType` en Admin (sin `company_id`): `country_id` required,
  name, `rate` (%), `equivalence_surcharge` nullable, status Active/Inactive,
  softDeletes.
- CRUD Configuración: Index ServerTable plano con columna País + filtro; Create/
  Edit modales; Acciones toggle + Edit + Delete; sin columna Estado; Active-first
  + name.
- Selector de país: **todos** los countries (Active e Inactive).
- Functionality `vat-types` bajo módulo `admin`; permisos `vat-types.*`; menú
  Configuración → Tipos de IVA.
- Seeder: tipos v1 → Country España (id 66); IDs preservados si el origen los
  trae; README § Seeders.
- Glosario / ficha Admin / module-map (Tax consume VatType; CRUD en Admin).

## Capabilities

### New Capabilities

- `admin-vat-types-catalog`: catálogo VatType por Country, CRUD Configuración y
  seeder España.

### Modified Capabilities

- `menu-core-pillars`: Configuración SA incluye Tipos de IVA.

## Impact

- Dominio Admin: model, migration, Actions, Policy, FormRequests, controller,
  Resource, routes `admin/vat-types*`, Inertia, i18n, MenuBuilder, seeder.
- Dependencia de lectura a Country (geografía).
- Tests feature (authz, country required, orden, seed, cross-company).
- E2E: N/A. Sin pivots cliente/proveedor. Sin dominio Tax code folder aún.
