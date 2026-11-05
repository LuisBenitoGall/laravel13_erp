# Design

## Context

Brief canónico: `docs/prompts/c2026-11-05-menu-sort.md`. `MenuCatalogCache`
ordena Modules y Functionalities Active con `orderBy('slug')`; `MenuBuilder`
preserva ese orden. Columnas `sort` nullable ya migradas. Este artefacto
reescribe el propose previo (salto de proceso explore) alineado al brief.

## Goals / Non-Goals

**Goals:**

- Criterio cerrado Luis: `sort` ASC, NULLS LAST, desempate `slug` ASC — raíces
  e hijos.
- Spec `menu-visibility` con el requisito y escenarios del brief (+ desempate
  explícito).
- Cache coherente tras el cambio de orden.

**Non-Goals:**

- UI / formularios / drag-and-drop para editar `sort` (no pedido en brief; no
  ampliar).
- Migración de columna (ya existe).
- Cambiar AND, capas SaaS, mapa de Modules del realign.
- Sembrar valores `sort` masivos (Luis ya puso algunos).
- Ficha `docs/modules/*` nueva; module-map; E2E (`e2e.md` §4) — no aplican.

## Decisions

### D1 — Criterio SQL (cerrado)

```
ORDER BY (sort IS NULL) ASC, sort ASC, slug ASC
```

Laravel/MySQL: `orderByRaw('`sort` is null')` + `orderBy('sort')` +
`orderBy('slug')` en Modules y en la relación Functionalities del `with`.

**Descartado:** solo `orderBy('sort')` (NULLS primero en MySQL ASC; sin
desempate estable).

### D2 — Punto único: MenuCatalogCache

Ordenar al materializar el payload cacheado. No reordenar en React/aside.
Bump `KEY` → `menu.catalog.active.v3` y `forget()` de `v2` / legado para evitar
payload stale ordenado por slug.

Incluir `sort` en el array serializado: opcional; no requerido si el orden de
la lista ya es definitivo. Preferencia: **no** inflar payload salvo que un test
lo necesite — 20 puede omitirlo.

### D3 — Modelos

Añadir `sort` a `$fillable` de `Module` y `Functionality` para que CUD futuro
no descarte el campo. Sin campos de formulario en este change.

### D4 — Scope de spec

Solo delta **ADDED** en `menu-visibility` (capability del menú). No hace falta
MODIFIED de requisitos existentes ni tocar `menu-core-pillars`.

### D5 — Vinculaciones / R-TEN-05

Sin entidad tenant nueva: V-01..V-20 N/A; R-TEN-05 N/A (el menú ya filtra por
company; este change solo reordena el catálogo global Active).

## Risks / Trade-offs

- [Tests acoplados a orden alfabético por slug] → actualizar a `sort`+slug.
- [Filas sin `sort`] → al final; comportamiento aceptado (NULLS LAST).
- [Cache stale post-deploy] → bump KEY + forget.

## Migration Plan

1. Fillable `sort`.
2. Order + KEY v3 en `MenuCatalogCache`.
3. Feature tests orden.
4. Pint / PHPStan paths tocados.

Rollback: revertir orderBy a slug y KEY a v2.

## Open Questions

Ninguna.
