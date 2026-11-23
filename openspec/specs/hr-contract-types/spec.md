## Purpose

Catálogo de tipos de contrato laboral por empresa (`ContractType`), base para
asignar periodos de empleo en RRHH.

## Requirements

### Requirement: Schema ContractType
El sistema SHALL persistir `contract_types` con `company_id`, `name`, `slug`,
`duration` en días (nullable), `with_holidays`, status activo/inactivo, y soft
deletes. SHALL aplicar `BelongsToCompany` y aislamiento de tenant.
`duration` null SHALL significar tipo **indefinido** (sin duración en días);
`duration` ≥ 1 SHALL significar tipo temporal. SHALL NOT usar 0 ni sentinels.

#### Scenario: Alta de tipo temporal
- **WHEN** un actor autorizado crea un tipo con name, duration (días ≥ 1) y
  campos válidos
- **THEN** queda persistido para la company de sesión y listable en el Index

#### Scenario: Alta de tipo indefinido
- **WHEN** un actor autorizado crea un tipo con duration null (indefinido)
- **THEN** queda persistido como indefinido y se presenta como tal en UI

#### Scenario: Aislamiento tenant
- **WHEN** un actor de company A lista o muta tipos
- **THEN** no ve ni altera tipos de company B

### Requirement: CRUD y soft-delete ContractType
El sistema SHALL ofrecer Index ServerTable, Create y Edit de ContractType con
authz `contract-types.*`. Soft-delete SHALL ocultar el tipo de altas nuevas de
empleo; los periodos históricos SHALL poder seguir referenciándolo.

#### Scenario: Soft-delete con histórico
- **WHEN** se soft-deletea un tipo con empleados históricos
- **THEN** deja de ofrecerse para altas nuevas y los periodos históricos
  siguen pudiendo referenciarlo

#### Scenario: Sin permiso
- **WHEN** un actor sin el permiso correspondiente fuerza Index o mutación
- **THEN** recibe 403
