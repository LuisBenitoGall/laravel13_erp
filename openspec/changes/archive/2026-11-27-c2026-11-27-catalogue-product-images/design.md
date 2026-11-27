# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-images.md`. Product CRUD y Edit
con tab General ya operativos (`Catalog/Products/*`, rutas `/products*`,
middleware `module:products`). Patrón de referencia: `UserImage` /
`UserImagesPanel` / disco `local` privado post foundation-hardening.
Dropzone del form-kit ya existe. v1: tabla única `product_docs` con mime +
featured — **no** clonar. E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** `ProductImage` + tab Imágenes; featured/public; disco privado;
entrega auth; elegibilidad Active∧public documentada/testeada; docs/tests.

**Non-Goals (design-level):** Documentos; Index thumbs; MediaPicker;
vista catálogo proveedor; cross-company file delivery completo (solo
gancho/regla; extensión R-TEN-10 en change futuro); deps nuevas.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Tabla propia `product_images` (no unificar con docs)
**Elección:** silo `product_images` paralelo a `user_images`. Documentos =
change siguiente `product_docs`.
**Descartado:** tabla única con flag/mime (v1): schemas, límites y UI
distintos; rompe el corte en 2 changes.

### D2 — Schema
- `product_id` FK cascade; `company_id` FK + índice (denormalizado desde
  Product al crear; R-TEN-05 / queries sin join obligatorio).
- Columna path (p. ej. `image` string 191) = path relativo en disco `local`:
  `products/{product_id}/images/{uuid}.{ext}` (o
  `companies/{company_id}/products/…` — preferir **con company_id en path**
  para inspección ops: `companies/{company_id}/products/{product_id}/images/…`).
- `featured` bool default false; `public` bool default false; SoftDeletes;
  timestamps.
- Unique un featured activo por producto: columna generada
  `featured_product_id` + unique (mismo patrón MySQL que UserImage; Action
  enforce en SQLite/tests).
- Unique `(product_id, image, deleted_at)` análogo a User.
- Factory `for(product)` + states `featured`/`public`.
- Al soft-delete: **borrar fichero** del disco en la Action (como UserImage
  actual).

### D3 — Elegibilidad fuera de Edit = **B** (Active ∧ public)
**Elección:** fuera del contexto de gestión de ficha, elegible solo si
`image.public === true` **y** `product.status === active`. Helper de
modelo/domain p. ej. `ProductImage::isEligibleOutsideEdit(Product): bool`.
**Descartado:** A (solo flag) — Inactive seguiría filtrando en catálogo
cliente; C (`product.is_public`) — tercer interruptor no pedido.
Cierra el A/B/C abierto del brief (recomendación explore).

### D4 — Authz / Policy
- Sin Spatie nuevo. `ProductImagePolicy` delega en `ProductPolicy` +
  `company_id` / `belongsToCurrentCompany` del Product.
- Mutaciones (upload/delete/featured/public): `ProductPolicy::update`.
- Stream file en gestión (Edit): `update` → cualquier imagen del producto.
- Stream / elegibilidad “consumo”: este change **no** abre cross-company;
  tests de elegibilidad son unit/feature sobre el helper. Cuando exista
  catálogo proveedor, un change ampliará `view` con R-TEN-10.
- Nombres Spatie actuales del Product (`catalogue.*` vs `products.*` según
  wiring vigente tras realign): **reutilizar exactamente** lo que ya usa
  `ProductPolicy`; no inventar slugs en este change.

### D5 — Rutas / Controller
Nested bajo product, p. ej.:
- `POST   /products/{product}/images`
- `GET    /products/{product}/images/{productImage}/file`
- `DELETE /products/{product}/images/{productImage}`
- `POST   /products/{product}/images/{productImage}/featured`
- `PATCH  /products/{product}/images/{productImage}/public`
Names `products.images.*`. Middleware: `auth` + `company` + `module:products`.
Actions: `UploadProductImages`, `DeleteProductImage`,
`MarkProductImageFeatured`, `UpdateProductImagePublic`.
Config `config/product_images.php` (mismos defaults que `user_images`).

### D6 — UI
- Tab `images` tras `general` en `Edit.tsx`.
- Panel `ProductImagesPanel` calcado a `UserImagesPanel` (Dropzone, grid,
  lightbox, Star, Eye/public, Trash + ConfirmDialog).
- Props: lista de imágenes con URL de entrega auth; `can.manage_images`
  desde `ProductPolicy::update`.
- Sin auto-featured al subir (como User).

### D7 — Vinculaciones V-01..V-21
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | No | Sin permisos Spatie nuevos |
| V-08 | No | Sin ítem menú nuevo |
| V-09 | No | Module ya existe |
| V-10..V-19 | No | |
| V-20 | No | ProductImage no Auditable (como UserImage) |
| V-21 | Sí | Rutas bajo `module:products` |
| R-TEN-05 | Sí | `company_id` + scope Product |
| E2E | N/A | Feature HTTP |

### D8 — Docs
Glosario: `ProductImage` / Imagen de producto. Ficha `Catalog.md`: oleada
imágenes; NonGoals docs/index. module-map: sin dependencia nueva entre
dominios.

## Risks / Trade-offs

- [Spec main `catalogue-products` aún cita `/admin/catalogue`] → este change
  no corrige el prefijo de rutas (ya realineado a `/products` en código);
  solo modifica el requirement de tabs. Sync de URL = change/docs aparte
  si hace falta.
- [Cross-company file] → Mitigación: regla B + helper; Policy cross-tenant
  diferida; evitar URLs firmadas anónimas.
- [company_id desync] → Mitigación: setear siempre desde Product en Upload;
  no aceptar company_id del cliente.
- [20 hardcode] → config `product_images.*`.

## Migration Plan

1. Docs glosario + ficha Catalog.
2. Migración + model/factory + config.
3. Policy/Actions/Requests/Controller/routes.
4. Tab + ProductImagesPanel + i18n.
5. Tests Pest.
Rollback: drop `product_images`; quitar tab/rutas.

## Open Questions

Ninguna bloqueante (A/B/C cerrado en D3).
