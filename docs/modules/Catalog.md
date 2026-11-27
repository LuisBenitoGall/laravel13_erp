# Módulo Catalog — catálogo de productos

> Ficha playbook §1. Changes: `c2026-11-26-catalogue-product-crud`,
> `c2026-11-27-catalogue-product-images`, `c2026-11-27-catalogue-product-docs`.
> Dominio PHP: `App\Domains\Catalog`. Module SaaS **`products`** (Optional).
> Functionality **`catalogue`** (UI Catálogo → `/products`).

## Propósito y alcance

**Entra:**

- Entidad **Product** (`products`): CRUD por Company; nombre llano; tipo
  físico/servicio inmutable; status Inactive al crear; soft delete;
  Edit con `RecordEditLayout` + tab General; listado `/products`.
- **ProductImage** (`product_images`): tab Imágenes en Edit (físico y
  servicio); Dropzone; featured/public; disco `local` privado; entrega
  autenticada; elegibilidad fuera de Edit = Active ∧ public.
- **ProductDoc** (`product_docs`): tab Documentos en Edit (después de
  Imágenes); Dropzone; sin featured; `public`; disco `local` privado;
  descarga autenticada; elegibilidad fuera de Edit = Active ∧ public.
- Module `products` + Functionality `catalogue` + menú.

**No entra:**

- Traducciones del nombre; SKU/código; unidades; IVA; cuentas.
- Familias, categorías, tags, atributos.
- Precios versionados, tarifas A/B/C, catálogo proveedor.
- Thumbs en Index; MediaPicker/Galería; OCR/preview/versionado docs.
- Consumo Sales/Inventory/Purchasing; entrega cross-company (R-TEN-10).

## Entidades

| Entidad | Tabla | Dominio | Notas |
|---|---|---|---|
| `Product` | `products` | Catalog | `BelongsToCompany`; soft delete; `name`, `type`, `status` |
| `ProductImage` | `product_images` | Catalog | `product_id` + `company_id`; path disco `local`; featured ≤1; public; soft delete (borra fichero) |
| `ProductDoc` | `product_docs` | Catalog | `product_id` + `company_id`; path disco `local`; sin featured; public; soft delete (borra fichero) |

## Dependencias

- **Admin** (Catalog → Admin = D): Company de sesión (`CurrentCompany`).
- Sin dependencia de dominio nueva por imágenes/docs (mismo Catalog → Admin).

## Librerías

Ninguna fuera del canon (`docs/standards/libraries.md`). Reutiliza Dropzone
del form-kit.

## Permisos y roles

| Functionality slug | Permisos Spatie | Roles seed (matriz) |
|---|---|---|
| `catalogue` | `catalogue.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |

Imágenes y documentos: sin Spatie nuevo; mutaciones/stream/descarga en Edit
vía `ProductPolicy::update`.

Module level: **Optional** (`products`). Ruta listado: `/products`.

## Referencia v1

| v1 | v2 |
|---|---|
| `Product` / Livewire CRUD | `Product` Inertia + ServerTable (MVP campos) |
| Tipo físico vs servicio | `ProductType` `physical`\|`service` inmutable |
| `product_docs` (mime + featured) | Split: `product_images` + `product_docs` (sin featured en docs) |
| Precios / familias / … | Changes posteriores |

## Migración de datos v1→v2

**No aplica** en estas oleadas (sin import masivo).

## Checklist de vinculaciones (§6)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí (CRUD) / No (imágenes/docs) | `catalogue.*` + `products.access`; adjuntos sin Spatie nuevo |
| V-08 | Sí | Menú → `/products` |
| V-09 | Sí | Module `products` Optional |
| V-10..V-19 | No | |
| V-20 | Sí (Product) / No (ProductImage/ProductDoc) | Auditable Product; adjuntos como UserImage/UserDoc |
| V-21 | Sí | Rutas `/products*` (+ `/images*`, `/docs*`) bajo `module:products` |
| R-TEN-05 | Sí | Product + ProductImage + ProductDoc con `company_id` |
| E2E | N/A | Feature HTTP basta |
