# Módulo Catalog — catálogo de productos

> Ficha playbook §1. Changes: `c2026-11-26-catalogue-product-crud`,
> `c2026-11-27-catalogue-product-images`, `c2026-11-27-catalogue-product-docs`,
> `c2026-11-27-catalogue-product-families`,
> `c2026-11-27-catalogue-product-categories`,
> `c2026-11-27-catalogue-product-tags`,
> `c2026-11-27-catalogue-product-classification`.
> Dominio PHP: `App\Domains\Catalog`. Module SaaS **`products`** (Optional).
> Functionalities: **`catalogue`** (Artículos → `/products`), **`families`**
> (Familias → `/products/families`), **`categories`** (Categorías →
> `/products/categories`), **`tags`** (Etiquetas → `/products/tags`).
> Menú grupo **Catálogo**.

## Propósito y alcance

**Entra:**

- Entidad **Product** (`products`): CRUD por Company; nombre llano; tipo
  físico/servicio inmutable; status Inactive al crear; soft delete;
  Edit con `RecordEditLayout` + tabs General / Imágenes / Documentos;
  listado `/products`.
- **Clasificación** en Edit → General: Family 0..1 (`family_id`),
  Categories 0..N (`category_product`), Tags 0..N (`product_tag`);
  pickers Active (+ ya vinculados); alta in situ de Tag Active si
  `tags.create` (`POST /products/tags/quick`). Create de producto sigue
  solo nombre+tipo.
- **ProductImage** / **ProductDoc**: tabs en Edit; disco `local` privado;
  elegibilidad fuera de Edit = Active ∧ public.
- **Family** (`families`): maestro plano; slug auto; CRUD
  `/products/families`; vínculo opcional Product↔Family 0..1.
- **Category** (`categories`): árbol N (`parent_id`); anti-ciclo; delete
  bloqueado con hijos; CRUD `/products/categories`; pivote Product↔Category.
- **Tag** (`tags`): maestro plano (≠ CSV v1); slug auto; status;
  Auditable; CRUD `/products/tags`; pivote Product↔Tag.
- Module `products` + Functionalities `catalogue` + `families` +
  `categories` + `tags` + menú.

**No entra:**

- Traducciones del nombre; SKU/código; unidades; IVA; cuentas.
- Tab Clasificación; Family↔Category N:N (**abandonado** — no pendiente).
- Precios versionados, tarifas A/B/C, catálogo proveedor.
- Thumbs en Index; MediaPicker/Galería; OCR/preview/versionado docs.
- Consumo Sales/Inventory/Purchasing; entrega cross-company (R-TEN-10).

**Entra (Index):** filtros avanzados ServerTable en Artículos (`catalog.products`)
con presets personales (máx. 5) + filterable name/type/status/familia/
categoría/etiqueta.

**Backlog:** precios/tarifas.

## Entidades

| Entidad | Tabla | Dominio | Notas |
|---|---|---|---|
| `Product` | `products` | Catalog | `BelongsToCompany`; soft delete; `name`, `type`, `status`, `family_id` nullable |
| `ProductImage` | `product_images` | Catalog | path disco `local`; featured ≤1; public |
| `ProductDoc` | `product_docs` | Catalog | path disco `local`; sin featured; public |
| `Family` | `families` | Catalog | `BelongsToCompany`; `name`, `slug`, `status`; Auditable; Product 0..1 |
| `Category` | `categories` | Catalog | `parent_id` árbol N; Auditable; pivote `category_product` |
| `Tag` | `tags` | Catalog | `BelongsToCompany`; `name`, `slug`, `status`; Auditable; pivote `product_tag` |

## Dependencias

- **Admin** (Catalog → Admin = D): Company de sesión (`CurrentCompany`).
- Sin dependencia de dominio nueva.

## Librerías

Ninguna fuera del canon (`docs/standards/libraries.md`).

## Permisos y roles

| Functionality slug | Permisos Spatie | Roles seed (matriz) |
|---|---|---|
| `catalogue` | `catalogue.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |
| `families` | `families.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |
| `categories` | `categories.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |
| `tags` | `tags.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |

Imágenes, documentos y clasificación en ficha: mutaciones vía
`ProductPolicy::update` (`catalogue.update`). Quick-create tag: `tags.create`.

Module level: **Optional** (`products`). Rutas: `/products`, `/products/families`,
`/products/categories`, `/products/tags`, `/products/tags/quick`.

## Referencia v1

| v1 | v2 |
|---|---|
| `Product` / Livewire CRUD | `Product` Inertia + ServerTable (MVP campos) |
| Category 2 niveles | `Category` árbol N (`parent_id`) |
| Tags CSV multilocal | `Tag` entidad por company |
| Menú Catálogo | Module `products` + functionalities |
| Clasificación ficha | Edit/General: Family / Categories / Tags |
| Precios / tarifas / … | Changes posteriores |
| Family↔Category N:N | **Abandonado** (no backlog) |

## Migración de datos v1→v2

**No aplica** en estas oleadas (sin import masivo).

## Checklist de vinculaciones (§6)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí | `catalogue.*` + `families.*` + `categories.*` + `tags.*` + `products.access` |
| V-08 | Sí | Menú Catálogo → Artículos / Familias / Categorías / Etiquetas |
| V-09 | Sí | Module `products` Optional (ya existía) |
| V-10..V-19 | No | |
| V-20 | Sí (Product, Family, Category, Tag) / No (adjuntos) | Auditable |
| V-21 | Sí | Rutas `/products*` bajo `module:products` |
| R-TEN-05 | Sí | Product + adjuntos + Family + Category + Tag con `company_id` |
| E2E | N/A | Feature HTTP basta |
