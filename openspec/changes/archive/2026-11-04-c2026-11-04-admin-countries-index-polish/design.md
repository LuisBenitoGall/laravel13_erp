# Design

## Context

See proposal.md — Why. `ServerTable` fija `width: 130` en Acciones (con
`fitColumns` el espacio sobrante engorda la columna; `hozAlign: 'right'` deja el
hueco a la izquierda). El formatter ignora `RowAction.icon` y pinta SVG Pencil /
trash. Countries/Provinces ya pasan `ListTree` (no se ve). Indexes geográficos
declaran columna `status` además del toggle.

## Goals / Non-Goals

**Goals:** Iconos Lucide reales; ancho Acciones sin hueco; Indexes geo sin Estado.

**Non-Goals:** Cambiar CRUD/API; quitar status de modales; rediseñar otros Indexes
más allá del arreglo genérico ServerTable.

## Decisions

### D1 — Render de `action.icon` con Lucide
**Elección:** En el formatter, si `action.icon` está definido, usar
`createElement(action.icon, { size: 14, strokeWidth: 2 })` +
`renderToStaticMarkup` (mismo patrón que Modules Index para iconos de celda).
Danger sin `icon` → trash SVG actual; con `icon` → ese Lucide.
**Descartado:** Seguir hardcodeando Pencil; mapa id→svg manual.

### D2 — Ancho Acciones por slots + widthGrow 0
**Elección:** `widthGrow: 0` y `width` calculado:
`padding + nToggle * slotToggle + nButton * slotButton` (constantes ~36–40px),
tomando el **máximo** de acciones entre una muestra: al construir columnas, si
`rowActions` existe, no hay filas aún → usar techo configurable por defecto
**4 slots** (cubre Countries: toggle+3) o prop opcional `actionsColumnSlots`.
Tras primer `dataLoaded`, opcionalmente `table.setColumnWidth('acciones', …)` si
el max real difiere (nice-to-have; no obligatorio si el techo 4 basta).
**Descartado:** Solo subir `width` a 200 fijo (sigue creciendo con fitColumns si
hay grow); alinear centro sin quitar grow.

### D3 — Indexes geo
**Elección:** Quitar columna `status` de los tres Indexes. Sustituir `ListTree` por
`Map` (Countries→provincias) y `MapPin` (Provinces→poblaciones). Towns sin drill.

### D4 — E2E
N/A. Verificación: inspección UI + tests existentes que no aserten columna Estado
en table JSON (Resource puede seguir enviando `status` para el toggle).

## Risks / Trade-offs

- [Modules/Roles Acciones cambian de ancho] → Mitigación: widthGrow 0 + slots
  mejora o neutro; menos hueco.
- [Iconos Lucide en formatter DOM] → Mitigación: markup estático SVG, sin hydration
  React dentro de Tabulator (igual que hoy).

## Migration Plan

Deploy front only. Rollback: revert ServerTable + tres Indexes.

## Open Questions

Ninguna.
