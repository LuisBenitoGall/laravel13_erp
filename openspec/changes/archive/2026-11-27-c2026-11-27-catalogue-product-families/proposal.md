# Proposal

## Why

La taxonomía del catálogo arranca por maestros. Family es el primer eje
(plano, 0..1 en producto más adelante): hace falta el CRUD por company y
abrir el menú Catálogo al estilo v1 (Artículos + Familias) antes de
Category/Tag y de clasificar productos.

## What Changes

- Entidad **`Family`** (`families`): `company_id`, nombre llano, slug auto,
  status Active/Inactive; SoftDeletes; CRUD ServerTable + Create/Edit.
- Functionality **`families`** bajo Module `products`; permisos
  `families.*`; menú Familias.
- Reorganizar menú del Module `products`: grupo con al menos **Artículos**
  (Functionality `catalogue` → listado productos) + **Familias**; labels
  alineadas a v1 (grupo Catálogo).
- Rutas bajo `module:products` (p. ej. `/products/families*`).
- Docs: glosario, ficha Catalog, module-map; tests Pest (CRUD, slug,
  R-TEN-05, menú/V-21).
- **No** vínculo Product↔Family; **no** Category/Tag; **no** Family↔Category.

## Capabilities

### New Capabilities

- `catalogue-product-families`: maestro Family por Company (schema, CRUD,
  menú, authz).

### Modified Capabilities

- `catalogue-module`: menú del Module `products` deja de ser hoja única;
  incluye Familias (y relabel Artículos/Catálogo según design).

## Impact

- `App\Domains\Catalog` (Family model/Actions/Policy/Controller/pages).
- `RealignMenuCatalog`, `MenuFunctionalityHref`, sync manager permissions.
- Sin deps nuevas. E2E §4: **N/A**.
- Sequencia: Category → Tag → clasificación ficha → (luego) Family↔Category.
