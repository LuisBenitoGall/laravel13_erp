# Spec Delta

## Purpose

Catálogo de tipos de jornada (`DayType`) por Company para clasificar los días
de los calendarios laborales de las sedes del tenant.

## ADDED Requirements

### Requirement: Catálogo DayType por Company
El sistema SHALL persistir tipos de jornada con `company_id` (BelongsToCompany),
soft delete, y al menos name, color e indicador de día laborable (`is_working`).
SHALL NOT anclar tipos de jornada a Workplace.

#### Scenario: Aislamiento tenant
- **WHEN** un usuario de company A lista tipos de jornada
- **THEN** no ve ni muta tipos de company B

### Requirement: CRUD Tipos de jornada bajo RRHH
El sistema SHALL exponer CRUD (Index ServerTable + Create/Edit) de DayType bajo
Module `rrhh` (Functionality `day-types`) con permisos Spatie canónicos y
entrada de menú **Tipos de jornada**.

#### Scenario: Menú y alta
- **WHEN** un actor con `day-types.view` / `day-types.create` opera RRHH
- **THEN** puede abrir Tipos de jornada y crear un tipo para la company en
  sesión

### Requirement: No borrar DayType referenciado
El sistema SHALL rechazar el borrado de un DayType mientras existan
`WorkCalendarDay` que lo referencien.

#### Scenario: Tipo en uso
- **WHEN** un actor intenta eliminar un tipo usado en al menos un día de
  calendario laboral
- **THEN** la operación se rechaza y el tipo permanece
