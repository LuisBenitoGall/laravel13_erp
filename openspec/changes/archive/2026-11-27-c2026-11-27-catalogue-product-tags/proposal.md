# Proposal

## Why

Cierra el trío de maestros de taxonomía: Tag como entidad real por company
(sustituye el CSV de v1), con menú bajo Catálogo, antes del change de
clasificación en la ficha Product (0..N tags opcionales).

## What Changes

- Entidad **`Tag`** (`tags`): `company_id`, nombre llano, slug auto,
  status Active/Inactive; SoftDeletes; CRUD ServerTable + Create/Edit.
- Functionality **`tags`** bajo Module `products`; permisos `tags.*`;
  menú Etiquetas.
- Rutas bajo `module:products` (p. ej. `/products/tags*`).
- Docs + tests (CRUD, slug, R-TEN-05, menú/V-21).
- **No** pivote Product↔Tag; **no** Family↔Category.

## Capabilities

### New Capabilities

- `catalogue-product-tags`: maestro Tag por Company (schema, CRUD, menú,
  authz).

### Modified Capabilities

- `catalogue-module`: Functionalities/menú del Module `products` incluyen
  Etiquetas/`tags` junto a Artículos, Familias y Categorías.

## Impact

- `App\Domains\Catalog` (Tag model/Actions/Policy/Controller/pages).
- `RealignMenuCatalog`, `MenuFunctionalityHref`, sync manager.
- Sin deps nuevas. E2E §4: **N/A**.
- Siguiente: clasificación en ficha Product → (luego) Family↔Category.
