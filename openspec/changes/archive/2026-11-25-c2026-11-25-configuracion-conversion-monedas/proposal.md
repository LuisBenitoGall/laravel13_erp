# Proposal

## Why

Antes de monedas por Company y de documentos comerciales con FX, hace falta un
maestro global de **cotizaciones** que el SA pueda mantener a mano. Sin él no
hay fuente de verdad para convertir importes; el catálogo `Currency` solo
nombra monedas, no tipos de cambio.

## What Changes

- Functionality `exchange-rates` bajo Module `configuracion`, label UI
  **Conversión monedas**, permisos Spatie canónicos, ítem de menú tras Monedas.
- Entidad global `ExchangeRate` (sin `company_id`): par dirigido
  `from_currency_id` → `to_currency_id`, `rate` (6 decimales), `rate_date`,
  unique `(from, to, rate_date)`.
- CRUD Configuración: Index `<ServerTable>` + Create/Edit con **dos campos**
  (A→B y B→A) que persisten dos filas; ayuda opcional “Rellenar inverso”
  (`1/rate`); sentidos independientes tras editar.
- Acción/helper de resolución: cotización vigente = mayor `rate_date` ≤ D para
  el par dirigido (aunque sea de días atrás). Norma de **snapshot** en
  documentos futuros documentada; sin enganchar pedidos/facturas.
- Glosario + ficha Admin + module-map actualizados.
- Tests Pest: CRUD, unique/upsert, resolución ≤ D, permisos, catálogo global.

## Capabilities

### New Capabilities

- `admin-exchange-rates`: maestro global de cotizaciones (CRUD Configuración,
  resolución vigente ≤ D, norma snapshot aplazada).

### Modified Capabilities

- `menu-core-pillars`: ítem **Conversión monedas** bajo Module `configuracion`.

## Impact

- Dominio Admin: migration/model/factory/policy/Actions/FormRequests/Resource/
  Controller/routes, páginas Inertia Index + Create/Edit, i18n, RealignMenu /
  seeder Functionality.
- Consume `Currency` Active (alta); no modifica el catálogo de monedas.
- Sin API externa, sin monedas por Company, sin FX en documentos (oleadas
  posteriores).
- E2E §4: N/A (flujo no catalogado).
- Excepción normativa: precisión del `rate` a **6** decimales (fuera de
  R-BD-02 3/4/2) — detalle en design.
