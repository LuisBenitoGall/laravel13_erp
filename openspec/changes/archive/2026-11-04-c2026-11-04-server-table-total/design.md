# Design

## Context

See proposal.md — Why. `TableBuilder::json()` ya responde `data`, `last_page`,
`total`. `ServerTable` `ajaxResponse` solo reenvía `data`/`last_page`. Footer
Tabulator existe (paginador derecha); izquierda vacía en captura.

## Goals / Non-Goals

**Goals:** `Total: N` i18n en pie izquierdo; sync con cada respuesta ajax; global.

**Non-Goals:** Rango de página; doble total; cambios TableBuilder; opt-in.

## Decisions

### D1 — Fuente del número = `response.total`
**Elección:** En `ajaxResponse`, leer `response.total` (number) y actualizar el
contador del pie. Ese valor ya refleja filtros del protocolo table.
**Descartado:** Contar filas de la página actual; pedir un endpoint aparte.

### D2 — DOM en `.tabulator-footer` + flex
**Elección:** Tras crear la tabla (o en `dataLoaded` / al devolver ajaxResponse),
asegurar un nodo `.server-table-total` como primer hijo (o slot izquierdo) del
`.tabulator-footer`. CSS: footer `display: flex; align-items: center;
justify-content: space-between` (o el paginador empuja a la derecha con
`margin-left: auto`). Texto: `t('total_registros', { count: n })` o
`t('total') + ': ' + n` según claves existentes/nuevas.
**Descartado:** Solo React sibling fuera del footer Tabulator (rompe alineación
con el paginador nativo).

### D3 — Sin prop opt-in
**Elección:** Siempre visible cuando hay paginación remota ServerTable.
**Descartado:** `showTotal={false}` por Index.

### D4 — i18n
**Elección:** Clave dedicada p. ej. `total_label` = «Total: {{count}}» (es/en/ca)
para un solo string interpolado (opción A del brief).

### D5 — E2E
N/A. Smoke: Index Países (o Modules) muestra el texto tras load y tras filtrar.

## Risks / Trade-offs

- [Footer Tabulator re-render] → Mitigación: reinyectar/actualizar nodo en cada
  `ajaxResponse`/`dataLoaded` si Tabulator limpia el footer.
- [total undefined en error] → Mostrar `Total: 0` o ocultar hasta primera respuesta
  OK (preferir `0` tras respuesta válida; no flash engañoso).

## Migration Plan

Front-only. Rollback: revert ServerTable + i18n + CSS.

## Open Questions

Ninguna.
