# Proposal

## Why

Tras el tab Imágenes de Product, la ficha Edit necesita adjuntos no-imagen
(fichas técnicas, PDF/Office) con el mismo patrón de Usuarios docs, disco
privado y flag `public` para que clientes del proveedor (dentro del ERP)
puedan ver/descargar solo lo marcado público.

## What Changes

- Nueva entidad **`ProductDoc`** / tabla `product_docs` (`product_id`,
  `company_id`, path, `original_name`, `mime`, `size_bytes`, `public`;
  soft delete; disco privado). **Sin** `featured`.
- Tab **Documentos** en `Catalog/Products/Edit` (tras Imágenes): Dropzone,
  listado, descarga, toggle public, eliminar.
- Rutas autenticadas store/download/destroy/public bajo el recurso Product;
  authz vía permisos de Product (sin Functionality nueva).
- Config de límites alineada a User docs (30 × 20 MB; pdf/office).
- Docs: glosario + ficha Catalog; tests Pest.
- **No** Index, **no** catálogo proveedor, **no** share, **no** Galería.

## Capabilities

### New Capabilities

- `catalogue-product-docs`: adjuntos documento de Product (schema, tab Edit,
  public, disco privado, descarga autenticada, elegibilidad Active∧public).

### Modified Capabilities

- `catalogue-products`: la ficha Edit incluye el tab Documentos operable
  tras Imágenes (y General).

## Impact

- Dominio `App\Domains\Catalog` (+ model/migration/factory ProductDoc).
- Routes `routes/domains/catalog.php`; panel en Edit.
- Reutiliza Dropzone del form-kit; sin deps nuevas.
- E2E §4: **N/A**.
- Dependencia de orden: preferible apply Imágenes antes (tab order +
  patrón Policy/path). Change Index thumbs = posterior.
