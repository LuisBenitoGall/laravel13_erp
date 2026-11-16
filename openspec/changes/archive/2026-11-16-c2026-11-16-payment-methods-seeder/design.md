# Design

## Context

CRUD `PaymentMethod` ya entregado (name JSON + status; sin code/slug).
Brief fija set 1–12 y upsert por id (patrón Banks/Units). v1 tenía 13
filas con 3× Transferencia y Cheque Pagaré ≠ Pagaré → limpio en set.

## Goals / Non-Goals

**Goals:** Seeder idempotente; ES/EN/CA; ids fijos; docs/test.

**Non-Goals:** code/slug; pivot; import legacy; UI; DatabaseSeeder
obligatorio (como Banks: invocable a mano; Index no importa).

**E2E:** no aplica.

**Excepciones:** ninguna.

## Decisions

### D1 — Clase y upsert

- `Database\Seeders\PaymentMethodsSeeder`.
- Upsert por `id` (columnas: `name` JSON string, `status` = `active`,
  timestamps; `deleted_at` null al upsert → restore si soft-deleted).
- No `delete()` masivo; no tocar ids fuera de 1–12.
- Tras upsert MySQL: reset AUTO_INCREMENT si el patrón Banks lo hace.

### D2 — Set canónico (ids)

| id | es (label) |
|----|------------|
| 1 | Contado en efectivo |
| 2 | Cheque/pagaré |
| 3 | Confirming |
| 4 | Domiciliación bancaria |
| 5 | Recibo |
| 6 | Talón |
| 7 | Transferencia |
| 8 | Tarjeta de crédito |
| 9 | A concretar |
| 10 | Abono de tarjetas en banco |
| 11 | Compensación |
| 12 | Bizum |

EN/CA equivalentes en el seeder (10-architecture fija traducciones
razonables; no inventar semántica distinta).

### D3 — Registro

- NO en `DatabaseSeeder` (alineado Banks/Sectors/Units).
- Documentar invocación en Admin.md.
- Feature test: seed dos veces → 12 ids; names JSON; Active; sin
  code/slug columns.

### D4 — V-01..V-20

Ningún sí nuevo (V-07/V-08 ya del CRUD). Solo datos.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Colisión id con datos manuales en dev | Upsert sobrescribe name/status de 1–12; documentar |
| Sin code para import futuro | Aceptado (brief) |

## Migration Plan

1. Añadir seeder + test.
2. Ejecutar seed en apply local.
3. Archive.

## Open Questions

Ninguna.
