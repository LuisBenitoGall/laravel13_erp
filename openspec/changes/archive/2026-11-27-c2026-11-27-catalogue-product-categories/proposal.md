# Proposal

## Why

Tras el maestro Family, el segundo eje de taxonomía es Category: árbol por
company para clasificar productos más adelante. Hace falta el CRUD y el
ítem de menú Categorías bajo Catálogo antes del change Tag y de la
clasificación en ficha.

## What Changes

- Entidad **`Category`** (`categories`): `company_id`, nombre llano, slug
  auto, status Active/Inactive, `parent_id` nullable (árbol N niveles);
  SoftDeletes; CRUD + menú.
- Functionality **`categories`** bajo Module `products`; permisos
  `categories.*`; menú Categorías.
- Rutas bajo `module:products` (p. ej. `/products/categories*`).
- Docs + tests (CRUD, parent/ciclos, R-TEN-05, menú/V-21).
- **No** Product↔Category; **no** Family↔Category; **no** Tag.

## Capabilities

### New Capabilities

- `catalogue-product-categories`: maestro Category por Company (schema
  árbol, CRUD, menú, authz).

### Modified Capabilities

- `catalogue-module`: Functionalities/menú del Module `products` incluyen
  Categorías junto a Artículos y Familias.

## Impact

- `App\Domains\Catalog` (Category model/Actions/Policy/Controller/pages).
- `RealignMenuCatalog`, `MenuFunctionalityHref`, sync manager.
- Sin deps nuevas. E2E §4: **N/A**.
- Siguiente: Tag → clasificación ficha → (luego) Family↔Category.
