# Proposal

## Why

El tab «Centros de trabajo» del Edit Delegación usa un listado HTML mínimo
(solo nombre + badge de preferida-por-sede). Eso no sigue el estándar
`<ServerTable>`, no muestra ubicación/fecha, y el badge masivo confunde con
una «sede preferida de la Delegación» que aún no existe. Hace falta la tabla
canónica y el eje B2 (≤1 sede preferida por Delegación) sin tocar el mapa
preferida-por-sede del kernel.

## What Changes

- Sustituir `MatrixWorkplacesLinkedPanel` por `<ServerTable>` de sedes
  vinculadas (endpoint table bajo Delegaciones).
- Columnas: nombre (enlace Edit sede), CP, población, provincia, país,
  `created_at`, sede preferida de la Delegación (nueva). Sin columna
  `is_preferred` (preferida-por-sede).
- Nueva marca en pivote `workplace_delegation` (B2): ≤1 sede preferida por
  Delegación; opcional (cero válido); editable desde Edit Delegación.
  Conservar `is_preferred` (preferida por Workplace) intacta.
- Glosario / Admin.md alineados; tests Pest (table, ≤1, perímetro, eje sede
  intacto).

## Capabilities

### New Capabilities

- `delegation-linked-workplaces`: ServerTable de sedes vinculadas a una
  Delegación + sede preferida por Delegación (B2).

### Modified Capabilities

- `workplace-delegation-map`: lectura inversa = ServerTable; añadir eje
  preferida por Delegación sin alterar preferida por sede.
- `company-delegations`: Edit tab centros usa ServerTable + preferida B2.

## Impact

- Migration pivote (columna nueva); Action set preferred seat; table endpoint
  + authz `delegations` / update + perímetro matriz.
- Frontend Edit Delegación tab workplaces; i18n (label distinto de
  `delegacion_preferida`).
- Kernel § grupo / invariantes docs: **sin cambio** (default docs sigue =
  preferida de la sede).
- E2E §4: N/A.
