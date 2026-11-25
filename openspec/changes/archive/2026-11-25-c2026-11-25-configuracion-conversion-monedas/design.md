# Design

## Context

See proposal.md — Why. Existe `Currency` global (Configuración → Monedas) y no
hay tipos de cambio en v1 ni en v2. Patrón a calcar: catálogos Admin `--global`
(Currencies/Units) + Functionality bajo `configuracion` +
`MenuFunctionalityHref` / `RealignMenuCatalog` / `CONFIGURATION_SLUGS`. Brief:
`docs/prompts/c2026-11-25-configuracion-conversion-monedas.md`.

## Goals / Non-Goals

**Goals:**

- Modelo global `ExchangeRate` + CRUD Configuración «Conversión monedas».
- UI par+fecha con ambos sentidos; resolución vigente `rate_date` ≤ D.
- Norma snapshot documentada para docs futuros; tests de dominio de resolución.
- Glosario, ficha `docs/modules/Admin.md`, fila Admin en `module-map.md`.

**Non-Goals:**

- API/proveedor externo; monedas por Company; FX en presupuestos/pedidos/
  facturas; redondeo de importes convertidos; bid/ask; seeder de cotizaciones;
  E2E browser.

## Decisions

### D1 — Naming: `ExchangeRate` / Functionality `exchange-rates`
**Elección:** Inglés normativo `ExchangeRate`; slug `exchange-rates`; label UI
i18n **Conversión monedas**. Rutas `/admin/exchange-rates*`, names
`admin.exchange-rates.*`.
**Descartado:** `CurrencyConversion` (más largo, confunde con conversión de
importe); `FxRate` (jerga). Añadir fila al glosario en este change.

### D2 — Catálogo global (excepción R-TEN-01)
**Elección:** Sin `company_id` / sin `BelongsToCompany`; generador `--global`.
Test cross-company “mismo catálogo” en lugar de R-TEN-05 aislamiento.
**Descartado:** rates por Company (eso es oleada company_currencies + uso).

### D3 — Esquema de fila
**Elección:**
- `from_currency_id` / `to_currency_id` FK → `currencies` (`restrict` on delete).
- `rate` `decimal(18,6)` cast `decimal:6` — **multiplicador**: 1 unidad origen →
  `rate` unidades destino.
- `rate_date` `date` (día calendario, sin timezone de negocio).
- Unique `(from_currency_id, to_currency_id, rate_date)` entre no soft-deleted
  (mismo patrón unique-with-trashed que monedas si aplica).
- SoftDeletes + Auditable; sin enum de status (la “vigencia” es por fecha, no
  Active/Inactive).
**Descartado:** una sola fila bidireccional (impide rates asimétricos); rate
como “unidades origen por destino”; status Active (redundante con historial).

### D4 — Excepción a R-BD-02 (6 decimales)
**Elección:** El `rate` usa **6** decimales (brief). No aplicar 3/4/2 de
importes. Documentado en «Excepciones a standards». Importes convertidos
siguen R-BD-02 en changes futuros.
**Descartado:** 4 decimales (insuficiente para pares frágiles); float.

### D5 — Upsert por par+fecha+sentido
**Elección:** Al guardar Create/Edit, si ya existe la fila
`(from,to,rate_date)` no trashed → **actualizar** `rate` (no 422 de unique).
Delete = soft delete de la fila dirigida.
**Descartado:** rechazar duplicate con 422 (peor UX en el form dual).

### D6 — UI Create/Edit dual + Index por fila dirigida
**Elección:**
- Index ServerTable: una fila = un `ExchangeRate` dirigido. Columnas: origen
  (code), destino (code), rate (6 dec), rate_date; Acciones Edit → Delete
  confirm. Default sort: `rate_date` desc, luego codes.
- Create / Edit: página o modal con select moneda A, moneda B (≠), fecha, rate
  A→B, rate B→A; CTA «Rellenar inverso» cliente (`1/rate`, 6 dec); al guardar
  Action upserta **ambas** filas en una transacción. Edit precarga ambos
  sentidos del par unordered + fecha (si falta un sentido, campo vacío → no
  crea el inverso vacío; rate required > 0 para cada sentido que se envíe).
- Ambos rates del form son independientes tras editar (no auto-recalc al blur
  salvo el botón explícito).
**Descartado:** Index agrupado por par (rompe ServerTable 1 entidad/fila);
forzar siempre ambos sentidos (SA puede anotar solo un sentido si quiere).

### D7 — Monedas elegibles
**Elección:** Selectores Create/Edit: `Currency` **Active** no trashed. Filas
históricas pueden referenciar monedas luego Inactive (Index sigue mostrando
codes). `from_id !== to_id`.
**Descartado:** exigir matriz completa de pares; permitir mismo origen=destino.

### D8 — Resolución vigente
**Elección:** Action/query `ResolveExchangeRate` (o nombre equivalente):
inputs `from`, `to`, `CarbonInterface|date D` → `ExchangeRate|null` con mayor
`rate_date` ≤ D. Sin “inventar” inverso desde el otro sentido. Sin rate → null
(consumidores futuros deciden error). Tests unit/feature de dominio en este
change; sin hook a documentos.
**Descartado:** exigir cotización exacta del día D; auto-invertir B→A.

### D9 — Snapshot (norma aplazada)
**Elección:** Spec + design fijan: documentos comerciales **SHALL** guardar el
rate aplicado (como precio de producto). Este change **no** añade columnas.
**Descartado:** implementar snapshot ahora.

### D10 — Menú / Functionality
**Elección:** Functionality `exchange-rates` en Module `configuracion`
(AdminOnly, mismo patrón Monedas); permisos `exchange-rates.*`; ítem menú
**después de Monedas** en `CONFIGURATION_SLUGS` / RealignMenu; href
`admin.exchange-rates.index`. Middleware `EnsureModuleIsActive:configuracion`.
**Descartado:** Module SaaS nuevo; colgar bajo Monedas como sub-CRUD.

### D11 — Sin seeder de rates
**Elección:** Catálogo vacío al migrar; SA carga a mano. README: no hace falta
entrada de seeder (salvo mención breve “sin seed”).
**Descartado:** seed EUR↔USD inventado.

### D12 — E2E
N/A (`e2e.md` §4).

### D13 — Vinculaciones (playbook §6)
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06, V-09..V-19 | No | Maestro global Configuración |
| V-07 | Sí | Functionality `exchange-rates` + Spatie |
| V-08 | Sí | Configuración → Conversión monedas |
| V-20 | Sí | Auditable / activity log como Currency |
| V-21 | Sí | Feature acceso: sin permiso → 403; SA/capas OK |
| R-TEN-05 | N/A | Global; test same-catalog cross-company |
| E2E | N/A | Feature Pest |

## Risks / Trade-offs

- [Rates asimétricos A→B ≠ 1/(B→A)] → Aceptado; botón inverso es ayuda, no
  invariante.
- [Sin cotización el día D] → Carry-forward ≤ D; si no hay ninguna ≤ D → null.
- [FK restrict al borrar Currency] → Delete Currency con rates → bloquear o
  soft-delete Currency (ya soft); documentar en tests si delete Currency falla.
- [6 dec vs R-BD-02] → Excepción explícita; no contagiar a importes.

## Migration Plan

1. Migration `exchange_rates` + artefactos dominio.
2. Seed Functionality + RealignMenu / href map.
3. Deploy: catálogo vacío; sin backfill.
4. Rollback: revert code + drop table (sin datos de negocio previos).

## Excepciones a standards

- **R-BD-02**: el campo `rate` de `ExchangeRate` usa **6** decimales
  (`decimal(18,6)` / cast `decimal:6`), no la tríada 3/4/2 de importes. El resto
  del sistema de dinero sigue R-BD-02.
- **R-TEN-01**: catálogo global `--global` (mismo patrón Currency).

## Open Questions

Ninguna bloqueante. Oleadas posteriores (company_currencies, docs FX, API)
fuera de este design.
