<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-23-rrhh-employee-overlap-scheduled
Rule: 10-architecture
Summary: Extender el no-solape de periodos Employee (opción B) a cualquier periodo
  `scheduled` o `active` de la misma company (solape de intervalos en calendario);
  mantener el cierre de `active` en Create/Renew (indefinido → D−1) sin que ese
  cierre se autobloquee.
Goals:
- Reemplazar la opción A (solo vs `active` con `end_date`) por opción B: en alta o
  renovación, rechazar si el nuevo periodo solapa en calendario con cualquier
  periodo `scheduled` o `active` del mismo user en la misma company (fin null =
  abierto; `ended` no cuenta).
- Solape entre companies distintas sigue permitido.
- Matiz: los `active` que **esta** operación va a cerrar (Renew del current, o
  Create cuando el nuevo nace `active`) NO bloquean el assert; se cierran como
  hoy (indefinido → end D−1). Los `scheduled` que solapan SÍ bloquean (caso real
  visto en BD: dos scheduled solapados en ACME).
- Renew SHALL excluir el periodo que cierra del assert (`exceptEmployeeId`).
- Actualizar spec `hr-employees` (MODIFIED no-solape) + tests Pest del caso
  scheduled+scheduled y del renew/create que cierra active.
NonGoals:
- Limpiar/migrar los 2 periodos ya existentes en BD local (dato de prueba).
- Solape contra periodos `ended`.
- Cambiar el cron de estados (sync scheduled→active / active→ended).
- Create ya no cierra actives (se descarta la B “más dura”: solo Renew cierra).
- Columna estado en tab Empleos / otros cambios de UI de ficha User.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
Notes:
- Origen: `/opsx-explore` tras ver en tab Empleos dos periodos ACME (CEO
  indefinido 25/11∞ `scheduled` + Temporal 24/11–10/01 `scheduled`) del mismo
  user; opción A + NonGoal “solo vs active” lo permitían. Ambos status
  confirmados en BD `laravel13_erp`.
- Código actual: `AssertEmployeeNoOverlap` solo mira `active` con end_date;
  early-return si indefinido. Create solo cierra actives si el nuevo es active.
- Change **nuevo** (cron-overlap y empleos-tab ya archivados/cerrados según
  contexto). Capacidades: delta `hr-employees`.


## ADDED Requirements

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

---

## MODIFIED Requirements

### Requirement: No solape con el active de la misma company (hr-employees)
La regla de no solape SHALL ampliarse de “solo active con end_date” a solape de
intervalos frente a `scheduled` y `active` (opción B), con el matiz de cierre de
actives descrito arriba. El requisito de renovar indefinido con D−1 se mantiene.

#### Scenario: Solape rechazado misma company (ampliado)
- **WHEN** hay un periodo no-ended (scheduled o active con fin) que solapa con
  el nuevo en la misma company
- **THEN** se rechaza, salvo que ese periodo sea un active cerrado por esta
  misma operación
