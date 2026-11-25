# Tasks

## 1. Datos — unicidad sin rate_date

- [x] 1.1 Migración: borrar todas las filas de `exchange_rates`; drop unique
      `exchange_rates_pair_date_unique` e índice `rate_date`; drop columna
      `rate_date`; unique `(from_currency_id, to_currency_id)`; verificar
      `migrate`
- [x] 1.2 Model `ExchangeRate`: quitar `rate_date` de fillable/casts; factory
      sin fecha
- [x] 1.3 Actualizar ficha `docs/modules/Admin.md` + `glossary.md` (sin
      historial ≤ D / sin `rate_date` de negocio)

## 2. Backend

- [x] 2.1 `UpsertExchangeRatePair`: upsert por `(from,to)` sin fecha; Create
      FormRequest rechaza si el par unordered ya tiene algún sentido activo
- [x] 2.2 `ResolveExchangeRate`: solo `(from, to)` → fila actual o null (sin
      fecha); actualizar callers/tests
- [x] 2.3 Controller `table`/Resource: una fila por par unordered **sin**
      `rate_date`; Update/Store sin fecha
- [x] 2.4 Feature Pest: reject Create par duplicado; Edit in-place; resolve
      actual; delete ambos sentidos; Index una fila/par; authz intacta

## 3. Frontend

- [x] 3.1 Index: columnas Par | A→B | B→A (number 6 dec) | Acciones; quitar
      Fecha; widthGrow en Par
- [x] 3.2 Create/Edit: sin campo fecha; labels `1 X = … Y`; CTA rellenar
      inverso editable; i18n si hace falta

## 4. Calidad

- [x] 4.1 Pint `--dirty` + PHPStan en PHP tocado; suite ExchangeRate verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
