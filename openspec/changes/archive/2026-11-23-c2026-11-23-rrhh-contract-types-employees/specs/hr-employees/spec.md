# Spec Delta

## Purpose

Periodos laborales (`Employee`) de un User en una company: alta, renovación y
cierre, distintos de la membresía `user_companies`.

## ADDED Requirements

### Requirement: Schema Employee periodo laboral
El sistema SHALL persistir `employees` con `company_id`, `user_id`,
`contract_type_id`, `workplace_id` nullable, `job_title`, `start_date`,
`end_date` nullable, `weekly_hours`, `time_control`, `year_salary` nullable,
`status` enum string `scheduled|active|ended`, `observations`, soft deletes.
SHALL aplicar `BelongsToCompany`. SHALL NOT tratar `user_companies` como empleo.

#### Scenario: Persistencia con company_id
- **WHEN** se crea un periodo válido
- **THEN** queda con `company_id` de sesión y FKs del mismo tenant

### Requirement: Alta exige membresía y User existente
El alta de un periodo SHALL exigir User existente y fila en `user_companies`
para la company de sesión. SHALL NOT crear User en ese flujo. ContractType y
Workplace (si se informa) SHALL pertenecer a la misma company.

#### Scenario: Alta solo con membresía
- **WHEN** un actor autorizado da de alta un periodo para un User sin
  membresía en la company
- **THEN** se rechaza

#### Scenario: Solo User existente
- **WHEN** se inicia el alta de empleo
- **THEN** se elige un User ya existente (no se crea User en ese flujo)

### Requirement: end_date según duración del tipo
Si el `ContractType` elegido tiene `duration` no null, el periodo SHALL exigir
`end_date`. Si el tipo es indefinido (`duration` null), `end_date` SHALL ser
opcional (habitualmente null; sin vencimiento implícito).

#### Scenario: Tipo temporal exige fecha fin
- **WHEN** se crea o renueva un periodo con un tipo cuya duration ≥ 1 sin
  `end_date`
- **THEN** se rechaza la operación

#### Scenario: Tipo indefinido sin fecha fin
- **WHEN** se crea un periodo con tipo indefinido (duration null) y sin
  `end_date`
- **THEN** el periodo se persiste con `end_date` null

### Requirement: Un solo periodo active y renovación
SHALL haber como máximo un Employee con status `active` por par
(user, company). Renovar SHALL cerrar el periodo activo (`ended`) y crear un
periodo nuevo en la misma transacción.

#### Scenario: Un solo activo
- **WHEN** un User ya tiene un periodo `active` y se renueva o se crea otro
  que quedaría activo
- **THEN** el anterior queda `ended` y el nuevo es el activo

#### Scenario: Renovación
- **WHEN** un actor autorizado renueva el empleo de un User
- **THEN** se cierra el periodo activo y se crea un periodo nuevo (no basta
  con solo cambiar el tipo sobre la misma fila sin cerrar)

### Requirement: Index y authz employees
El sistema SHALL listar periodos de la company de sesión (ServerTable) con
authz `employees.*` y aislamiento de tenant.

#### Scenario: Aislamiento
- **WHEN** un actor de company A lista empleados
- **THEN** no ve periodos de company B

#### Scenario: Sin permiso
- **WHEN** un actor sin `employees.view` (y sin Super Admin) fuerza el Index
- **THEN** recibe 403
