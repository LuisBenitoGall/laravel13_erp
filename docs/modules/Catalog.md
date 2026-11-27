# Módulo Catalog — catálogo de productos

> Ficha playbook §1. Changes: `c2026-11-26-catalogue-product-crud`,
> `c2026-11-27-catalogue-product-images`, `c2026-11-27-catalogue-product-docs`,
> `c2026-11-27-catalogue-product-families`,
> `c2026-11-27-catalogue-product-categories`.
> Dominio PHP: `App\Domains\Catalog`. Module SaaS **`products`** (Optional).
> Functionalities: **`catalogue`** (Artículos → `/products`), **`families`**
> (Familias → `/products/families`), **`categories`** (Categorías →
> `/products/categories`). Menú grupo **Catálogo**.

## Propósito y alcance

**Entra:**

- Entidad **Product** (`products`): CRUD por Company; nombre llano; tipo
  físico/servicio inmutable; status Inactive al crear; soft delete;
  Edit con `RecordEditLayout` + tabs General / Imágenes / Documentos;
  listado `/products`.
- **ProductImage** / **ProductDoc**: tabs en Edit; disco `local` privado;
  elegibilidad fuera de Edit = Active ∧ public.
- **Family** (`families`): maestro por Company; slug auto; status;
  Auditable; CRUD en `/products/families`. **Sin** vínculo a Product.
- **Category** (`categories`): maestro por Company; árbol N niveles
  (`parent_id`); slug auto; status; Auditable; CRUD en
  `/products/categories`; anti-ciclo; delete bloqueado si hay hijos.
  **Sin** vínculo a Product ni Family↔Category.
- Module `products` + Functionalities `catalogue` + `families` +
  `categories` + menú.

**No entra:**

- Traducciones del nombre; SKU/código; unidades; IVA; cuentas.
- Vínculo Product↔Family/Category; Tag; Family↔Category N:N;
  clasificación en ficha Product.
- Precios versionados, tarifas A/B/C, catálogo proveedor.
- Thumbs en Index; MediaPicker/Galería; OCR/preview/versionado docs.
- Consumo Sales/Inventory/Purchasing; entrega cross-company (R-TEN-10).

**Backlog clasificación:** Tag → clasificación ficha → Family↔Category N:N.

## Entidades

| Entidad | Tabla | Dominio | Notas |
|---|---|---|---|
| `Product` | `products` | Catalog | `BelongsToCompany`; soft delete; `name`, `type`, `status` |
| `ProductImage` | `product_images` | Catalog | path disco `local`; featured ≤1; public |
| `ProductDoc` | `product_docs` | Catalog | path disco `local`; sin featured; public |
| `Family` | `families` | Catalog | `BelongsToCompany`; `name`, `slug`, `status`; Auditable |
| `Category` | `categories` | Catalog | `BelongsToCompany`; `parent_id` árbol N; `name`, `slug`, `status`; Auditable |

## Dependencias

- **Admin** (Catalog → Admin = D): Company de sesión (`CurrentCompany`).
- Sin dependencia de dominio nueva (Family/Category maestros en Catalog).

## Librerías

Ninguna fuera del canon (`docs/standards/libraries.md`).

## Permisos y roles

| Functionality slug | Permisos Spatie | Roles seed (matriz) |
|---|---|---|
| `catalogue` | `catalogue.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |
| `families` | `families.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |
| `categories` | `categories.{view,viewAny,create,update,delete}` + `products.access` | manager: CRUD; guest: view |

Imágenes y documentos: sin Spatie nuevo; mutaciones vía `ProductPolicy::update`.

Module level: **Optional** (`products`). Rutas: `/products`, `/products/families`,
`/products/categories`.

## Referencia v1

| v1 | v2 |
|---|---|
| `Product` / Livewire CRUD | `Product` Inertia + ServerTable (MVP campos) |
| Category 2 niveles | `Category` árbol N (`parent_id`) |
| Menú Catálogo → Artículos / Familias / Categorías | Module `products` + functionalities |
| Precios / Tag / … | Changes posteriores |

## Migración de datos v1→v2

**No aplica** en estas oleadas (sin import masivo).

## Checklist de vinculaciones (§6)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí | `catalogue.*` + `families.*` + `categories.*` + `products.access` |
| V-08 | Sí | Menú Catálogo → Artículos / Familias / Categorías |
| V-09 | Sí | Module `products` Optional (ya existía) |
| V-10..V-19 | No | |
| V-20 | Sí (Product, Family, Category) / No (adjuntos) | Auditable |
| V-21 | Sí | Rutas `/products*` bajo `module:products` |
| R-TEN-05 | Sí | Product + adjuntos + Family + Category con `company_id` |
| E2E | N/A | Feature HTTP basta |
