# Spec Delta

## ADDED Requirements

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

## MODIFIED Requirements

### Requirement: Un solo periodo active y renovación
SHALL haber como máximo un Employee con status `active` por par
(user, company). Renovar SHALL cerrar el periodo activo (`ended`) y crear un
periodo nuevo en la misma transacción. Alta o renovación SHALL respetar el no
solape frente al `active` de la misma company y el cierre de indefinidos con
fin D−1 cuando aplique.

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
