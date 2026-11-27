# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-families.md`. Product +
images/docs operativos. Module Optional `products`; Functionality hoja
`catalogue` (UI Catálogo → `/products`) — AsideNav colapsa a 1 hijo.
v1 menú: grupo Catálogo → Artículos, Familias, Categorías, …. E2E §4:
**N/A**.

## Goals / Non-Goals

**Goals:** Family CRUD; Functionality `families`; menú grupo Catálogo;
docs/tests.

**Non-Goals:** Product↔Family; Category/Tag; Family↔Category N:N;
traducciones; deps nuevas.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Schema Family
- Tabla `families`: `company_id` FK; `name` string; `slug` string;
  `status` enum string `active`|`inactive`; SoftDeletes; timestamps;
  Auditable (`created_by`/`updated_by`) como Product.
- Unique **`(company_id, slug)`** entre no borrados (mismo patrón soft
  delete que hermanos: unique con `deleted_at` o enforce en Action).
- **Sin** unique de `name` (homónimos permitidos; como Product MVP).
- Path/factory `for(company)` + states `active`/`inactive`.
- Enum `FamilyStatus` con `canTransitionTo` Active↔Inactive libre.

### D2 — Slug auto + status en Create
- Slug: generar desde `name` (patrón slug del proyecto / `Str::slug`);
  si colisión en company, sufijo numérico. Editable en Edit solo si 10
  ve riesgo — **decisión:** slug **RO tras create** (regenerar solo si
  name cambia y slug no fue “custom”; MVP: regenerar desde name en
  create; en update **no** retocar slug salvo Action explícita —
  **simplificar:** create genera slug; update **no** cambia slug).
- Status en Create: campo en form; **default Inactive** (alineado Product).
- Soft delete: **sí** (playbook maestro).

### D3 — Functionality `families` + menú v1
- Bajo Module `products` (Optional): Functionality slug **`families`**,
  permisos `families.{view,viewAny,create,update,delete}`.
- Labels Functionality: es **Familias** / en Families / ca Famílies.
- Relabel para alinear captura v1:
  - Module `products` label es → **Catálogo** (grupo).
  - Functionality `catalogue` label es → **Artículos** (antes “Catálogo”).
- `FUNCTIONALITY_MAP['products']` = `['catalogue', 'families', …]`.
- `MenuFunctionalityHref`: `families` → `/products/families`.
- Sync manager: incluir `families.*` en matriz manager (CRUD) / guest
  (view) como Product.
- AsideNav: con ≥2 functionalities el Module deja de ser hoja y muestra
  hijos — sin cambio de componente si el payload ya lista children.

### D4 — Rutas / pages
- Prefijo existente `products` + `module:products`.
- Registrar **antes** de `{product}`:
  - `GET/POST products/families`, `table`, `create`, `{family}/edit`,
    put/delete.
- Names `products.families.*`.
- Pages Inertia: `Catalog/Families/Index|Create|Edit`.
- Policy `FamilyPolicy`: Spatie `families.*` + `belongsToCurrentCompany`.

### D5 — Sin vínculo Product
No `family_id` en `products`; no pivote; no UI en Product Edit.

### D6 — Vinculaciones
| V | ¿Sí? | Nota |
|---|---|---|
| V-07 | Sí | `families.*` |
| V-08 | Sí | Menú Familias + relabel grupo |
| V-09 | No | Module ya existe |
| V-20 | Sí | Auditable Family |
| V-21 | Sí | `module:products` |
| R-TEN-05 | Sí | |
| E2E | N/A | |

### D7 — Docs
Glosario Family (ya hay fila; precisar Module/Functionality). Ficha
Catalog: oleada Family. module-map: Family operativo (maestro); vínculo
producto pendiente. Anotar backlog: Family↔Category N:N; Category;
Tag; clasificación ficha.

## Risks / Trade-offs

- [Relabel Module→Catálogo / catalogue→Artículos] → Mitigación: i18n +
  Realign; tests menú. Slugs Spatie `catalogue.*` **no** renombrar.
- [`{product}` vs `families`] → Registrar rutas families primero.
- [Slug inmutable en update] → Mitigación: documentado; change futuro si
  negocio pide renombrar slug.

## Migration Plan

1. Docs.
2. Migración + model/enum/factory.
3. Realign Functionality + href + sync permisos + Policy.
4. Backend CRUD + routes.
5. Frontend Index/Create/Edit + i18n menú.
6. Tests.
Rollback: drop `families`; soft-delete Functionality `families`; revert
labels.

## Open Questions

Ninguna bloqueante.
