# Design

## Context

See proposal.md — Why. Hoy: Countries/Provinces/Towns/Currencies aplican
`orderBy('name')` si `$request->sorts() === []`. Modules no tiene default.
`TableBuilder::applySorts()` solo ordena cuando el request trae sorts. Status =
string enum (`active` / `inactive`), no booleano.

## Goals / Non-Goals

**Goals:**

- Active-first solo en default (sin sorts cliente) en los cinco `table`.
- Conservar `name` como secundario donde ya existía; Modules sin secundario.

**Non-Goals:**

- Prefijo Active-first con sorts del cliente; otros Indexes; UI; TableBuilder
  API pública obligatoria.

## Decisions

### D1 — Solo cuando sorts vacío
**Elección:** Envolver el default actual en `if ($request->sorts() === [])`. Si
hay sorts, `TableBuilder` aplica solo los del cliente (sin CASE Active).
**Descartado:** Active-first siempre como prefijo — contradice el brief.

### D2 — Expresión de orden
**Elección:** `orderByRaw` / `orderBy` con CASE equivalente a
`CASE WHEN status = 'active' THEN 0 ELSE 1 END` (usar valor del enum PHP del
modelo, no magic string suelta si hay helper). Luego `orderBy('name')` donde
aplique.
**Descartado:** `orderByDesc('status')` lexicográfico — frágil si cambian
valores; boolean cast inexistente.

### D3 — Helper local vs TableBuilder
**Elección:** Helper pequeño reutilizable (p. ej. en `App\Support\Table` o
closure privada compartida) llamado desde los cinco controllers — evita 5
copias del CASE. **No** ampliar el contrato público de `ServerTable` front.
**Descartado:** meter default Active-first global en `TableBuilder` para todos
los listados — fuera de alcance (otros Indexes no).

### D4 — Modules sin secundario
**Elección:** Solo Active-first; orden dentro de cada grupo = el de la BD
(aceptable; Luis explícito).
**Descartado:** forzar `name` o `created_at` sin pedirlo.

### D5 — Cascada JSON geography
**Elección:** No tocar endpoints `options` (ya filtran Active). Solo Indexes
`table`.
**Descartado:** reordenar cascada — no pedido.

### D6 — E2E / vinculaciones
N/A. Tests feature: assert orden relativo Active antes Inactive sin sorts;
assert con sort por name no fuerza Active-first.

## Risks / Trade-offs

- [CASE + collation] → Usar valor enum canónico `active`; tests con factory
  Active+Inactive.
- [Modules sin secundario] → Orden interno no determinista entre mismos status;
  aceptado.

## Migration Plan

Deploy código; sin migración BD. Rollback = revert commits.

## Open Questions

Ninguna.
