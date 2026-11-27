# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-categories.md`. Family ya
operativo (patrón a calcar). Module `products` con `catalogue` +
`families`; menú grupo Catálogo. v1 Category = 2 niveles; v2 = N.
E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Category CRUD con árbol N; Functionality `categories`; menú;
docs/tests.

**Non-Goals:** Product↔Category; Family↔Category; Tag; traducciones;
deps nuevas.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Schema Category (parity Family + parent)
- Tabla `categories`: `company_id`; `name`; `slug`; `status`
  `active`|`inactive`; `parent_id` nullable FK → `categories`
  (`nullOnDelete` o restrict — ver D3); SoftDeletes; Auditable;
  timestamps.
- Unique `(company_id, slug)` (mismo patrón soft-delete que Family).
- Sin unique de `name`.
- Enum `CategoryStatus` (Active↔Inactive libre).
- Factory `for(company)` + states; state `childOf(Category)`.

### D2 — Slug / status (igual Family)
- Create: slug desde `name` (+ sufijo si colisión); status en form;
  **default Inactive**.
- Update: **no** retoca slug.
- Soft delete: **sí**.

### D3 — Árbol N niveles
- Adjacency list: `parent_id` nullable = raíz.
- Padre MUST misma `company_id`; MUST existir y no soft-deleted.
- **Anti-ciclo:** al asignar/cambiar parent, rechazar self y cualquier
  ancestro que sea descendiente del nodo (walk up from new parent / walk
  down from self).
- **Delete con hijos:** **bloquear** soft-delete si tiene hijos no
  borrados (mensaje i18n); no cascade automático. *Alt. descartada:*
  cascade soft-delete (sorpresa en árboles profundos); reparent a null
  (puede dejar huérfanos no deseados sin UI).
- FK `parent_id`: `nullOnDelete` a nivel BD no aplica bien con soft
  delete; enforce en Action + tests.
- Sin `sort` entre hermanos en este change (orden por `name` en listados).

### D4 — Index UI
- **ServerTable** (canon listados) con columnas name, parent (nombre o
  “—”), slug, status; búsqueda name/slug.
- Indentación visual opcional vía campo `depth`/`path` en Resource si es
  barato; no inventar tree-grid custom.
- Form Create/Edit: Select de padre = categorías de la company
  **excepto** el propio nodo y sus descendientes (en Edit).

### D5 — Functionality `categories` + menú
- Slug **`categories`**; permisos `categories.*`.
- Labels: es Categorías / en Categories / ca Categories (o Categoríes).
- `FUNCTIONALITY_MAP['products']` = `['catalogue','families','categories']`.
- Href → `/products/categories`.
- Sync manager: manager CRUD / guest view (como families).

### D6 — Rutas / pages
- Bajo `products` + `module:products`, **antes** de `{product}`:
  `products/categories*` (table/index/create/store/edit/update/destroy).
- Names `products.categories.*`.
- Pages `Catalog/Categories/*`.
- Policy `CategoryPolicy`: `categories.*` + company.

### D7 — Sin vínculos Product/Family
No pivotes; no UI en Product Edit; no `category_families`.

### D8 — Vinculaciones
| V | ¿Sí? | Nota |
|---|---|---|
| V-07 | Sí | `categories.*` |
| V-08 | Sí | Menú Categorías |
| V-09 | No | |
| V-20 | Sí | Auditable |
| V-21 | Sí | `module:products` |
| R-TEN-05 | Sí | |
| E2E | N/A | |

### D9 — Docs
Glosario Category (árbol N). Ficha Catalog + module-map. Backlog Tag →
clasificación → Family↔Category.

## Risks / Trade-offs

- [Árbol profundo] → Mitigación: anti-ciclo + bloqueo delete con hijos;
  tests.
- [`{product}` vs `categories`] → Rutas categories primero.
- [ServerTable vs tree UX] → Mitigación: parent column + select; tree UI
  rica = change futuro si hace falta.

## Migration Plan

1. Docs.
2. Migración + model/enum/factory.
3. Realign Functionality + href + sync + Policy.
4. Actions (create/update/delete + tree guards) + routes.
5. Frontend Index/Create/Edit + i18n.
6. Tests.
Rollback: drop `categories`; soft-delete Functionality `categories`.

## Open Questions

Ninguna bloqueante.
