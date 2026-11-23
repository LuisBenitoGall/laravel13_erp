## Purpose

Periodos laborales (`Employee`) de un User en una company: alta, renovación y
cierre, distintos de la membresía `user_companies`.

## Requirements

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
periodo nuevo en la misma transacción. Alta o renovación SHALL respetar el no
solape frente a periodos `scheduled`/`active` de la misma company y el cierre
de indefinidos con fin D−1 cuando aplique.

#### Scenario: Un solo activo
- **WHEN** un User ya tiene un periodo `active` y se renueva o se crea otro
  que quedaría activo
- **THEN** el anterior queda `ended` y el nuevo es el activo

#### Scenario: Renovación
- **WHEN** un actor autorizado renueva el empleo de un User
- **THEN** se cierra el periodo activo y se crea un periodo nuevo (no basta
  con solo cambiar el tipo sobre la misma fila sin cerrar)

#### Scenario: Renovación sin solape
- **WHEN** se renueva respetando start > end del active (o cierre D−1 si era
  indefinido)
- **THEN** el anterior queda ended y el nuevo es el active (o scheduled según
  fechas)

### Requirement: Cron de estados de periodos laborales
El sistema SHALL ejecutar de forma programada (diaria) una tarea multi-tenant
que actualice periodos `Employee`: `scheduled` → `active` si `start_date ≤ hoy`
y no exista ya otro `active` del mismo user en la misma company; `active` →
`ended` si tiene `end_date` y `end_date < hoy`. Periodos indefinidos
(`end_date` null) SHALL NOT caducar por el cron. Al activar, SHALL mantener
como máximo un `active` por (user, company).

#### Scenario: Scheduled llega su día
- **WHEN** corre el cron y existe un periodo `scheduled` con `start_date ≤ hoy`
  y no hay otro `active` del mismo user en esa company
- **THEN** ese periodo pasa a `active`

#### Scenario: Active vencido
- **WHEN** corre el cron y un periodo `active` tiene `end_date < hoy`
- **THEN** pasa a `ended`

#### Scenario: Indefinido no caduca solo
- **WHEN** un periodo `active` tiene `end_date` null
- **THEN** el cron no lo finaliza por caducidad

#### Scenario: Scheduled no activa si sigue un active
- **WHEN** corre el cron, un `scheduled` tiene `start_date ≤ hoy`, pero aún
  existe un `active` del mismo user en esa company
- **THEN** el `scheduled` permanece `scheduled`

### Requirement: No solape con el active de la misma company
La regla de no solape SHALL aplicar solape de intervalos frente a periodos
`scheduled` y `active` del mismo user en la misma company (opción B). Los
`active` que la propia operación cierra (Renew del current, o Create cuando el
nuevo nace `active`) SHALL NOT bloquear el assert. Periodos `ended` y otras
companies SHALL NOT participar. Renovar indefinido con fin D−1 se mantiene.

#### Scenario: Solape rechazado misma company
- **WHEN** el active de la company termina el 24/11/2026 y se intenta un nuevo
  periodo con inicio ≤ 24/11/2026 (y no es el active cerrado por esta operación)
- **THEN** se rechaza la operación

#### Scenario: Inicio al día siguiente OK
- **WHEN** el active termina el 24/11/2026 y el nuevo inicia el 25/11/2026
- **THEN** la operación puede aceptarse (resto de reglas OK)

#### Scenario: Solape entre companies permitido
- **WHEN** el user tiene un active en company A y se crea un periodo en
  company B con fechas solapadas
- **THEN** no se rechaza por la regla de solape de A

#### Scenario: Solape rechazado misma company (ampliado)
- **WHEN** hay un periodo no-ended (scheduled o active) que solapa con el nuevo
  en la misma company
- **THEN** se rechaza, salvo que ese periodo sea un active cerrado por esta
  misma operación

### Requirement: No solape calendario vs scheduled y active
En alta o renovación de un periodo en una company, el nuevo intervalo
(`[start_date`, `end_date` o abierto]) SHALL NOT solapar con ningún periodo
`scheduled` o `active` del mismo user en esa company, salvo los `active` que la
propia operación cierra en la misma transacción. SHALL NOT aplicarse entre
companies distintas. Periodos `ended` SHALL NOT participar en la regla.

#### Scenario: Dos scheduled solapados rechazados
- **WHEN** existe un periodo `scheduled` 25/11/2026→∞ y se intenta crear otro
  `scheduled` 24/11/2026→10/01/2027 en la misma company para el mismo user
- **THEN** se rechaza la operación

#### Scenario: Inicio el día siguiente al fin de un scheduled OK
- **WHEN** existe un `scheduled` o `active` con fin 24/11/2026 y el nuevo inicia
  el 25/11/2026 sin solapar con otros no-ended
- **THEN** la operación puede aceptarse (resto de reglas OK)

#### Scenario: Solape entre companies permitido
- **WHEN** el user tiene un scheduled/active en company A y se crea un periodo
  en company B con fechas solapadas
- **THEN** no se rechaza por la regla de solape de A

#### Scenario: Create/Renew que cierra active no se autobloquea
- **WHEN** se renueva un active (o se crea un periodo que nace active) y el
  active previo solapa en calendario con el nuevo
- **THEN** el previo se cierra (ended; indefinido con fin D−1) y el nuevo se
  crea; el assert no rechaza por ese active cerrado en la misma operación

### Requirement: Renovar indefinido cierra el día anterior
Renovar un periodo `active` indefinido (`end_date` null) SHALL estar permitido.
El periodo anterior SHALL quedar `ended` con `end_date` igual al día anterior
al `start_date` del nuevo periodo.

#### Scenario: Renovar indefinido
- **WHEN** un actor autorizado renueva un active indefinido con nuevo inicio D
- **THEN** el anterior queda ended con fin D−1 y el nuevo periodo se crea

### Requirement: Proyección de periodos en ficha User (lectura multi-company)
El sistema SHALL permitir listar, en la ficha Edit de User, todos los periodos
`Employee` del sujeto con `withoutGlobalScope('company')` (filtro `user_id`),
en solo lectura, cuando el change/spec de user-edit lo declare. SHALL NOT
mutar periodos desde esa proyección. SHALL NOT exigir permiso `employees.view`
para esa lectura (authz = acceso a la ficha). Esta proyección SHALL alinearse
con la excepción de lecturas ancladas a User documentada en kernel /
architecture (R-TEN-10).

#### Scenario: Listado cross-company por user_id
- **WHEN** un actor autorizado a la ficha solicita la tabla de periodos del User
- **THEN** recibe filas de todas las companies del sujeto (no solo CurrentCompany)

#### Scenario: Sin mutación desde la proyección
- **WHEN** el actor usa el tab Empleos de la ficha User
- **THEN** no puede crear, renovar, editar ni cancelar periodos desde ahí

### Requirement: Index y authz employees
El sistema SHALL listar periodos de la company de sesión (ServerTable) con
authz `employees.*` y aislamiento de tenant.

#### Scenario: Aislamiento
- **WHEN** un actor de company A lista empleados
- **THEN** no ve periodos de company B

#### Scenario: Sin permiso
- **WHEN** un actor sin `employees.view` (y sin Super Admin) fuerza el Index
- **THEN** recibe 403
