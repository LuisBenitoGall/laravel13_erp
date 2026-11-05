# Proposal

## Why

Las columnas `sort` ya existen en `modules` y `functionalities` (con valores),
pero el menú lateral sigue ordenando por `slug` vía `MenuCatalogCache`. Hace
falta un change acotado (aparte del realign) con contrato en spec.

Fuente: brief `docs/prompts/c2026-11-05-menu-sort.md`.

## What Changes

- Orden de raíces (Modules) e hijos (Functionalities) del menú:
  `sort` ASC, NULLS LAST, desempate `slug` ASC.
- Aplicar el criterio al construir el catálogo cacheado; invalidar/bump de clave.
- Delta en `menu-visibility` con el requisito de ordenación.
- Sin migración nueva de columna (ya está).
- Sin UI de edición/reordenado de `sort` (fuera del brief; no ampliar alcance).

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `menu-visibility`: ordenación normativa del menú por `sort` + desempate `slug`.

## Impact

- `MenuCatalogCache` (queries `orderBy`); tests de menú que asuman orden por slug.
- Modelos: `sort` fillable si hace falta para no perder el valor en CUD futuros.
- E2E: no aplica. Ficha de módulo nueva: no aplica (sin entidad nueva).
