# Proposal

## Why

Los cimientos Fase 0 están listos; el siguiente paso de producto es el primer
módulo de negocio. RRHH oleada 1 (tipos de contrato + empleados/periodos)
arranca el dominio HR con reglas de tenancy v2 y, a la vez, fija el **mapa de
capabilities** para que los agents no carguen todo el catálogo de specs en cada
change.

## What Changes

- Nuevo dominio `App\Domains\HR` y Module SaaS slug **`rrhh`** (Optional) con
  Functionalities `contract-types` y `employees`.
- Entidad **ContractType** (`contract_types`): CRUD tenant; duration en días;
  soft-delete; catálogo por company.
- Entidad **Employee** (`employees`): periodo laboral con `company_id` +
  `BelongsToCompany`; exige User ∈ `user_companies`; ≤1 `active` por
  user+company; renovar = cerrar + crear; solo User existente; enum
  `scheduled|active|ended`.
- Ficha `docs/modules/HR.md`; glosario Empleo/ContractType; module-map
  (Contract → ContractType).
- **Mapa de capabilities** publicado (`docs/standards/capabilities-map.md`) con
  RRHH como ejemplo de referencia.

## Capabilities

### New Capabilities

- `hr-contract-types`: catálogo de tipos de contrato laboral por company.
- `hr-employees`: periodos laborales (alta, renovación, unicidad active).
- `hr-module`: Module `rrhh`, Functionalities, menú data-driven, tres capas,
  permisos y acceso tenant (V-21).
- `capabilities-map`: índice on-demand de contexto para agents (patrón módulo
  nuevo + ejemplo RRHH).

### Modified Capabilities

_(ninguna)_

## Impact

- Código nuevo bajo `app/Domains/HR`, rutas `/hr/*`, pages Inertia HR.
- Lectura Admin: User, UserCompany, Workplace (sin escritura desde HR).
- Seed Module/Functionality/`rrhh.access` + sync manager según capas Optional.
- Docs: ficha HR, glossary, module-map, capabilities-map, índice standards.
- Tests Pest: R-TEN-05, membresía, ≤1 active, renovación, V-21; E2E N/A.
