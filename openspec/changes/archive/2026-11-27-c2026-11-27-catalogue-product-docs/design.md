# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-docs.md`. **Imágenes ya
implementadas** (`ProductImage`, tab Edit, rutas `products.images.*`,
elegibilidad Active∧public). Patrón a calcar: `ProductImage*` +
`UserDoc`/`UserDocsPanel` (metadatos + download). E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** `ProductDoc` + tab Documentos tras Imágenes; `public`; disco
privado; download auth; elegibilidad Active∧public; docs/tests.

**Non-Goals:** featured; Index; MediaPicker; vista catálogo proveedor;
share; deps nuevas; reabrir diseño de Imágenes.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Tabla `product_docs` (silo aparte)
**Elección:** paralelo a `product_images` / `user_docs`.
**Descartado:** unificar con imágenes (v1).

### D2 — Schema
- `product_id` FK cascade; `company_id` FK + índice (denormalizado en
  Upload desde Product).
- `path` string 191; `original_name` 255; `mime` 127; `size_bytes`
  unsignedBigInteger; `public` bool default false; SoftDeletes;
  timestamps.
- **Sin** `featured`.
- Unique `(product_id, path, deleted_at)`; índices `product_id`,
  `company_id`, `public`.
- Path disco `local`:
  `companies/{company_id}/products/{product_id}/docs/{uuid}.{ext}`.
- Al soft-delete: **borrar fichero** (como ProductImage/UserDoc).
- Factory `for(product)` + state `public`.
- Config `config/product_docs.php`: max_per_product 30, max_per_upload 10,
  max_kilobytes 20480, mimes pdf/doc/docx/xls/xlsx (como `user_docs`).

### D3 — Elegibilidad = misma B que Imágenes
`ProductDoc::isEligibleOutsideEdit()` = `public && product.status === Active`.
No reabrir A/B/C.

### D4 — Authz / Policy
`ProductDocPolicy` espejo de `ProductImagePolicy`: mutaciones y download en
Edit → `ProductPolicy::update` + company. Cross-company diferido.
Sin Spatie nuevo; reutilizar slugs que ya usa `ProductPolicy`.

### D5 — Rutas / Controller
Nested bajo product:
- `POST   /products/{product}/docs`
- `GET    /products/{product}/docs/{productDoc}/download`
- `DELETE /products/{product}/docs/{productDoc}`
- `PATCH  /products/{product}/docs/{productDoc}/public`
Names `products.docs.*`. Middleware igual que images.
Actions: `UploadProductDocs`, `DeleteProductDoc`,
`UpdateProductDocPublic`. Download = attachment (no inline obligatorio).

### D6 — UI
- Tab `docs` tras `images` en `Edit.tsx`.
- `ProductDocsPanel` calcado a `UserDocsPanel` + toggle public (como
  Eye en images).
- Props: docs + `can.manage_docs` + limits desde config.
- Edit controller: eager-load docs al render (mismo patrón images).

### D7 — Vinculaciones V-01..V-21
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-09 | No | (Module/menú/permisos ya existen) |
| V-10..V-20 | No | |
| V-21 | Sí | Rutas bajo `module:products` |
| R-TEN-05 | Sí | `company_id` + Product |
| E2E | N/A | Feature HTTP |

### D8 — Docs
Glosario: `ProductDoc` / Documento de producto. Ficha Catalog: oleada
docs. module-map: sin dependencia nueva.

## Risks / Trade-offs

- [Orden apply] → Imágenes ya en código; Documentos puede apply ya.
- [company_id desync] → setear solo desde Product en Upload.
- [MIME office] → mismos accept que UserDoc; validar FormRequest.

## Migration Plan

1. Docs glosario + ficha.
2. Migración + model/factory + config.
3. Policy/Actions/Requests/Controller/routes.
4. Tab + ProductDocsPanel + i18n + Edit props.
5. Tests Pest.
Rollback: drop `product_docs`; quitar tab/rutas.

## Open Questions

Ninguna bloqueante.
