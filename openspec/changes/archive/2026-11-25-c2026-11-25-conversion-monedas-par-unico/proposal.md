# Proposal

## Why

El maestro Conversión monedas modeló historial por `rate_date` (varias filas del
mismo par). El producto real es más simple: **un par existe una sola vez**, se
actualizan sus tasas in-place, y el historial lo congela la **operación**
(snapshot), no el catálogo.

## What Changes

- Unicidad por par dirigido `(from, to)` **sin fecha**; un par unordered
  (EUR/USD) no se puede dar de alta dos veces.
- Eliminar `rate_date` del modelo de negocio del maestro (drop columna +
  índices/unique antiguos).
- Resolución vigente = rate **actual** del par (sin “última ≤ D”).
- UI Index/Create/Edit: una fila = un par; sin fecha de cotización; inverso
  calculable y editable.
- Purga de filas de prueba actuales en migración.
- Specs `admin-exchange-rates` actualizadas; docs ficha/glosario si citan
  historial por fecha.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `admin-exchange-rates`: par único sin historial por fecha; resolve actual;
  Index/CRUD sin `rate_date`; inverso calculable/editable; purga de prueba;
  snapshot en operación sigue como norma aplazada.

## Impact

- Migration: truncate/delete `exchange_rates`, drop `rate_date` + unique
  `(from,to,rate_date)`, unique `(from,to)`.
- Actions `UpsertExchangeRatePair`, `DeleteExchangeRate`, `ResolveExchangeRate`.
- Controller table/Resource/FormRequests; Index React; tests Pest.
- E2E §4: N/A.
