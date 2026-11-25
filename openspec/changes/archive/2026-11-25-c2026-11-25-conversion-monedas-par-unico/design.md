# Design

## Context

See proposal.md — Why. Código actual: tabla `exchange_rates` con `rate_date`,
unique `(from,to,rate_date)`, Index agrupado por par+fecha, Resolve ≤ D.
Brief: `docs/prompts/c2026-11-25-conversion-monedas-par-unico.md`.

## Goals / Non-Goals

**Goals:**

- Par único; drop semántica/columna `rate_date`; resolve = rate actual;
  UI sin fecha; inverso calculable/editable; purga prueba; tests.

**Non-Goals:**

- Snapshot en docs comerciales; API FX; monedas por Company; cambiar Currency.

## Decisions

### D1 — Drop `rate_date`
**Elección:** Migración que (1) borra todas las filas de `exchange_rates`
(prueba), (2) drop unique/index de fecha, (3) drop columna `rate_date`.
**Descartado:** dejar columna nullable “por si acaso” — confunde y contradice
el brief (“la fecha no sirve”).

### D2 — Persistencia: dos filas dirigidas, unique `(from, to)`
**Elección:** Mantener dos filas EUR→USD y USD→EUR (como ahora) con unique
`(from_currency_id, to_currency_id)` entre no trashed. El “par lógico”
unordered se identifica por `LEAST/GREATEST` (CASE portable) en Index.
**Alta:** si ya existe **cualquier** sentido del par unordered → 422
validación (no segundo alta); Edit actualiza in-place.
**Descartado:** una sola fila con `rate_ab`/`rate_ba` (más migración de
código; dos dirigidas ya existen y sirven a Resolve por sentido).

### D3 — Create vs Edit ante “par ya existe”
**Elección:** Create **rechaza** con error de validación si el par unordered
ya existe (mensaje i18n). No auto-redirigir a Edit en este change.
**Descartado:** upsert silencioso en Create (ocultaba el error de “repetir”).

### D4 — ResolveExchangeRate = actual
**Elección:** `ResolveExchangeRate::handle(from, to): ?ExchangeRate` — la
única fila dirigida vigente (sin parámetro fecha). Firma anterior con fecha
se elimina o se ignora deprecando en design→tasks (romper callers de tests).
**Descartado:** keep ≤ D.

### D5 — UI
**Elección:** Index columnas Par | A→B | B→A | Acciones (sin Fecha). Create:
Moneda A/B + rates con «Rellenar inverso» editable. Edit: par readonly +
rates. Delete sigue soft-deleteando **ambos** sentidos del par.
**Descartado:** columna `updated_at` obligatoria en Index (opcional no
bloqueante; 20 puede omitirla).

### D6 — Inverso
**Elección:** CTA cliente `1/rate` a 6 dec; no auto-recalc al blur; persistir
lo enviado aunque ≠ `1/rate`.
**Descartado:** forzar simetría en backend.

### D7 — Docs
**Elección:** Actualizar ficha `docs/modules/Admin.md` (ExchangeRate) y
glosario si citan `rate_date`/historial ≤ D.
**E2E:** N/A (`e2e.md` §4).

### D8 — Vinculaciones
Sin V-07/V-08 nuevos (Functionality ya existe). V-20/V-21 intactos. R-TEN-05
N/A (global). Tests: unique par, reject duplicate create, resolve sin fecha,
delete ambos sentidos, Index sin rate_date.

## Risks / Trade-offs

- [Pérdida de historial de catálogo] → Aceptado; snapshot vivirá en docs.
- [Filas huérfanas de un solo sentido] → Create puede guardar un solo sentido;
  el par unordered sigue “ocupado”; Edit puede completar el otro.

## Migration Plan

1. DELETE/TRUNCATE `exchange_rates` (prueba).
2. Drop unique `exchange_rates_pair_date_unique` + index `rate_date`.
3. Drop column `rate_date`.
4. Unique `(from_currency_id, to_currency_id)` (nombre estable).
5. Deploy código + UI + tests.

Rollback: reverse migration re-adding `rate_date` (datos no recuperables).

## Excepciones a standards

Ninguna nueva. Se mantiene la excepción previa de `rate` a 6 decimales
(R-BD-02) y catálogo global (R-TEN-01).

## Open Questions

Ninguna bloqueante.
