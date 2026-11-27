# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-tags.md`. Family + Category
operativos (Tag = parity Family, plano). Module `products` con
`catalogue` / `families` / `categories`. v1 tags = CSV — descartado.
E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Tag CRUD; Functionality `tags`; menú; docs/tests.

**Non-Goals:** Product↔Tag; Family↔Category; árbol; traducciones; deps
nuevas.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Schema Tag (parity Family)
- Tabla `tags`: `company_id`; `name`; `slug`; `status` `active`|`inactive`;
  SoftDeletes; Auditable; timestamps.
- Unique `(company_id, slug)` (mismo patrón soft-delete que Family).
- Sin unique de `name`.
- Enum `TagStatus` (Active↔Inactive libre).
- Factory `for(company)` + states `active`/`inactive`.

### D2 — Slug / status (igual Family)
- Create: slug desde `name` (+ sufijo si colisión); status en form;
  **default Inactive**.
- Update: **no** retoca slug.
- Soft delete: **sí**.

### D3 — Functionality `tags` + menú
- Slug **`tags`**; permisos `tags.*`.
- Labels UI: es **Etiquetas** / en Tags / ca Etiquetes (slug EN `tags`).
- `FUNCTIONALITY_MAP['products']` =
  `['catalogue','families','categories','tags']`.
- Href → `/products/tags`.
- Sync manager: manager CRUD / guest view.

### D4 — Rutas / pages
- Bajo `products` + `module:products`, **antes** de `{product}`:
  `products/tags*` (table/index/create/store/edit/update/destroy).
- Names `products.tags.*`.
- Pages `Catalog/Tags/*`.
- Policy `TagPolicy`: `tags.*` + company.

### D5 — Sin vínculo Product
No pivote `product_tag`; no UI en Product Edit.

### D6 — Vinculaciones
| V | ¿Sí? | Nota |
|---|---|---|
| V-07 | Sí | `tags.*` |
| V-08 | Sí | Menú Etiquetas |
| V-09 | No | |
| V-20 | Sí | Auditable |
| V-21 | Sí | `module:products` |
| R-TEN-05 | Sí | |
| E2E | N/A | |

### D7 — Docs
Glosario Tag (entidad; ≠ CSV v1). Ficha Catalog + module-map. Backlog:
clasificación ficha → Family↔Category.

## Risks / Trade-offs

- [`{product}` vs `tags`] → Rutas tags primero.
- [Nombre modelo `Tag`] → OK en Catalog; evitar colisión con Spatie/otros
  si existiera — namespace `App\Domains\Catalog\Models\Tag`.

## Migration Plan

1. Docs.
2. Migración + model/enum/factory.
3. Realign Functionality + href + sync + Policy.
4. Backend CRUD + routes.
5. Frontend Index/Create/Edit + i18n.
6. Tests.
Rollback: drop `tags`; soft-delete Functionality `tags`.

## Open Questions

Ninguna bloqueante.
