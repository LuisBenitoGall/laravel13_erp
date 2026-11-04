# Proposal

## Why

Los Indexes ServerTable muestran paginador a la derecha y el pie izquierdo vacío.
Hace falta el total de registros del resultado actual (`Total: N`) en todas las
tablas, con y sin filtros, sin tocar cada Index.

## What Changes

- Wrapper `ServerTable`: pie izquierdo con i18n `Total: {n}`; alineado verticalmente
  con el paginador derecho.
- Usar `total` que ya envía `TableBuilder` (hoy descartado en `ajaxResponse`).
- Contrato en `server-table-reference`; sin opt-in por página.
- i18n es/en/ca para la etiqueta.

## Capabilities

### New Capabilities
- (ninguna)

### Modified Capabilities
- `server-table-reference`: total de registros en pie izquierdo del ServerTable.

## Impact

- `resources/js/Components/Table/ServerTable.tsx` (+ CSS footer si aplica).
- Claves i18n.
- Sin cambio obligatorio de `TableBuilder` (ya expone `total`).
- E2E N/A; verificación visual / smoke en un Index existente.
