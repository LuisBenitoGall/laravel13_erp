# Proposal

## Why

El ERP necesita un catálogo de productos (físicos y servicios) por empresa como
maestro para presupuestos, pedidos, albaranes, inventarios y tarifas. Se arranca
por fases; esta oleada es solo el CRUD mínimo de Product, base para el resto.

## What Changes

- Nuevo dominio PHP `App\Domains\Catalog` + ficha `docs/modules/Catalog.md` y
  actualización de `module-map` / glosario / capabilities-map.
- Module SaaS slug **`catalogue`** (Basic) + Functionality **`products`**;
  menú Catálogo → listado.
- Entidad **Product** (`products`): `BelongsToCompany`; nombre llano; tipo
  `physical`|`service` **inmutable** tras el alta; status inicial **Inactive**
  (solo servidor en Create); soft delete; CRUD ServerTable + Create + Edit con
  `RecordEditLayout` (tab General; tipo RO en `RecordSummary`).
- Rutas bajo prefijo **`/admin/catalogue`** (middleware `module:catalogue`).
- Tests Pest: CRUD, Inactive por defecto, tipo inmutable, R-TEN-05, V-21.

## Capabilities

### New Capabilities

- `catalogue-module`: Module SaaS `catalogue`, Functionality `products`, menú,
  tres capas y acceso tenant (V-21).
- `catalogue-products`: CRUD Product por Company (alta mínima, status, tipo
  inmutable, Edit con tabs).

### Modified Capabilities

_(ninguna — module-map/docs se actualizan por tasks de registro; menú nuevo
queda bajo `catalogue-module`, no exige delta de `menu-core-pillars`.)_

## Impact

- Código nuevo bajo `app/Domains/Catalog`, `routes/domains/catalog.php`, pages
  Inertia `Catalog/Products`.
- Seed Module/Functionality/`catalogue.access` + sync manager (Basic).
- Sin deps npm/composer nuevas. E2E §4: **N/A**.
- Fuera: traducciones, unidades/IVA, familias, precios, tarifas, proveedores,
  consumo Sales/Inventory.
