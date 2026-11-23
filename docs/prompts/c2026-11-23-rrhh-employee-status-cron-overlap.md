<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-23-rrhh-employee-status-cron-overlap
Rule: 10-architecture
Summary: Cron diario que actualiza estados de periodos laborales (scheduled→active,
  active→ended por vencimiento) y política de no solape de fechas frente al
  periodo active de la misma company; renovación de indefinido cierra el anterior
  el día previo al nuevo inicio.
Goals:
- Comando/scheduler multi-tenant (R-TRV-04) que, por company:
  - pase `scheduled` → `active` cuando `start_date ≤ hoy`;
  - pase `active` → `ended` cuando `end_date` no es null y `end_date < hoy`
    (caducidad); indefinidos (`end_date` null) no caducan solos;
  - al activar, respetar ≤1 `active` por (user, company).
- No solape (opción A): renovación o alta/cambio de periodo en la misma company
  frente al periodo **active** del mismo user: `nuevo.start_date` SHALL ser
  **estrictamente posterior** a `active.end_date` cuando el activo ya tiene fin
  (no igual ni anterior). Entre companies distintas el solape SÍ está permitido.
- Renovar cuando el active es indefinido: permitido; el anterior pasa a `ended`
  con `end_date` = día anterior a la fecha de inicio del nuevo periodo (sin
  solape).
- Basarse en el dominio HR / specs `hr-employees` ya archivadas; tests Pest de
  cron + solape + renovación indefinido.
NonGoals:
- Notificaciones/avisos de vencimiento próximos (visión; oleada posterior).
- Solape contra periodos `scheduled` o `ended` (solo contra `active`).
- Cambiar catálogo ContractType / indefinido = duration null (ya cerrado).
- Fichajes, calendarios, otros módulos.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
Notes:
- Origen: `/opsx-explore` post RRHH oleada 1; hoy el status solo se fija al
  create (`EmployeeStatus::initialFor`) y no hay cron; Renew/Create pueden
  dejar solapes si el active ya tenía `end_date` posterior al nuevo start.
- Change RRHH `c2026-11-23-rrhh-contract-types-employees` archivado: este es
  change **nuevo**.
- Scheduler: un job/comando idempotente por tenant (company_id explícito en
  jobs), no depender de CurrentCompany en CLI.


## ADDED Requirements

### Requirement: Cron de estados de periodos laborales
El sistema SHALL ejecutar de forma programada (diaria) una tarea multi-tenant
que actualice periodos `Employee`: `scheduled` → `active` si `start_date ≤ hoy`;
`active` → `ended` si tiene `end_date` y `end_date < hoy`. Periodos indefinidos
(`end_date` null) SHALL NOT caducar por el cron. Al activar, SHALL mantener
como máximo un `active` por (user, company).

#### Scenario: Scheduled llega su día
- **WHEN** corre el cron y existe un periodo `scheduled` con `start_date ≤ hoy`
- **THEN** ese periodo pasa a `active` (respetando ≤1 active por user+company)

#### Scenario: Active vencido
- **WHEN** corre el cron y un periodo `active` tiene `end_date < hoy`
- **THEN** pasa a `ended`

#### Scenario: Indefinido no caduca solo
- **WHEN** un periodo `active` tiene `end_date` null
- **THEN** el cron no lo finaliza por caducidad

### Requirement: No solape con el active de la misma company
En alta o renovación de un periodo en una company, si el mismo user ya tiene
un periodo `active` en esa company con `end_date` informado, el nuevo
`start_date` SHALL ser estrictamente posterior a ese `end_date`. SHALL NOT
aplicarse la restricción entre companies distintas.

#### Scenario: Solape rechazado misma company
- **WHEN** el active de la company termina el 24/11/2026 y se intenta un nuevo
  periodo con inicio ≤ 24/11/2026
- **THEN** se rechaza la operación

#### Scenario: Inicio al día siguiente OK
- **WHEN** el active termina el 24/11/2026 y el nuevo inicia el 25/11/2026
- **THEN** la operación puede aceptarse (resto de reglas OK)

#### Scenario: Solape entre companies permitido
- **WHEN** el user tiene un active en company A y se crea un periodo en
  company B con fechas solapadas
- **THEN** no se rechaza por la regla de solape de A

### Requirement: Renovar indefinido cierra el día anterior
Renovar un periodo `active` indefinido (`end_date` null) SHALL estar permitido.
El periodo anterior SHALL quedar `ended` con `end_date` igual al día anterior
al `start_date` del nuevo periodo.

#### Scenario: Renovar indefinido
- **WHEN** un actor autorizado renueva un active indefinido con nuevo inicio D
- **THEN** el anterior queda ended con fin D−1 y el nuevo periodo se crea

---

## MODIFIED Requirements

### Requirement: Un solo periodo active y renovación (hr-employees)
La renovación y el alta que dejen un nuevo periodo vigente SHALL incorporar la
regla de no solape frente al `active` de la misma company y el cierre de
indefinidos descrito arriba (además del ≤1 active ya existente).

#### Scenario: Renovación sin solape
- **WHEN** se renueva respetando start > end del active (o cierre D−1 si era
  indefinido)
- **THEN** el anterior queda ended y el nuevo es el active (o scheduled según
  fechas)
