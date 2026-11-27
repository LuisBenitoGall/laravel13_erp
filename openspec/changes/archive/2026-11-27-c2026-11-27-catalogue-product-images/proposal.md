# Proposal

## Why

El CRUD mínimo de Product ya existe; la ficha Edit solo tiene el tab General.
Hace falta adjuntar imágenes (portada + galería) con el mismo patrón que
Usuarios, disco privado y flag `public`, para que más adelante el Index, el
catálogo proveedor→cliente y otras vistas del ERP puedan mostrar fotos sin
rediseñar el storage.

## What Changes

- Nueva entidad **`ProductImage`** / tabla `product_images` (`product_id`,
  `company_id`, path, `featured`, `public`; soft delete; disco privado).
- Tab **Imágenes** en `Catalog/Products/Edit` (físico y servicio): Dropzone,
  grid/lightbox, featured, toggle public, eliminar.
- Rutas autenticadas de upload / file / featured / public / destroy bajo el
  recurso Product; authz vía `products.*` (sin Functionality nueva).
- Config de límites alineada a User imágenes (20 × 1 MB; jpg/png/gif/webp).
- Docs: glosario + ficha `Catalog.md`; tests Pest (límites, featured, public,
  perímetro, entrega auth).
- **No** Index thumbs, **no** Documentos, **no** MediaPicker/Galería, **no**
  vista catálogo proveedor.

## Capabilities

### New Capabilities

- `catalogue-product-images`: adjuntos imagen de Product (schema, tab Edit,
  featured/public, disco privado, entrega autenticada, reglas de elegibilidad
  fuera de ficha).

### Modified Capabilities

- `catalogue-products`: la ficha Edit pasa a incluir el tab Imágenes operable
  además de General (sin stubs Documentos u otros).

## Impact

- Dominio `App\Domains\Catalog` (+ model/migration/factory ProductImage).
- Routes `routes/domains/catalog.php`; pages `Catalog/Products/Edit` + panel.
- Reutiliza Dropzone del form-kit; sin deps npm/composer nuevas.
- E2E §4: **N/A** (feature HTTP + Pest basta; no flujo e2e.md §4).
- Sequencia posterior: change Documentos (`product_docs`); change Index/thumbs.
