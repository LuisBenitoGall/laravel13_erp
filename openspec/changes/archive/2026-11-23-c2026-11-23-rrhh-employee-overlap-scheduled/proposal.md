# Proposal

## Why

La opción A de no-solape solo miraba el periodo `active` con `end_date`. Dos
periodos `scheduled` del mismo user/company pueden solaparse en calendario
(caso real ACME: indefinido 25/11∞ + temporal 24/11–10/01). Hay que endurecer
a solape de intervalos frente a `scheduled` y `active` sin romper el cierre
de actives en Create/Renew.

## What Changes

- `AssertEmployeeNoOverlap` (opción B): rechazar si el nuevo intervalo solapa
  con cualquier `scheduled`/`active` de la misma company (fin null = abierto;
  `ended` fuera).
- Matiz: excluir del assert los `active` que la misma operación cierra (Renew
  del current vía `exceptEmployeeId`; Create cuando el nuevo nace `active`).
- Mantener cierre D−1 de indefinidos y solape cross-company permitido.
- Delta spec `hr-employees` + tests Pest (scheduled+scheduled rechazado;
  Create/Renew que cierra active OK).

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `hr-employees`: no-solape ampliado a scheduled+active (intervalos); matiz de
  cierre de actives.

## Impact

- `AssertEmployeeNoOverlap`, llamadas desde Create/Renew y FormRequests.
- Mensajes i18n de solape (si hace falta distinguir scheduled).
- Tests Feature HR de overlap. Sin migration BD. Sin UI nueva.
- E2E §4: N/A.
