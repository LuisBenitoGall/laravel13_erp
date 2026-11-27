# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-27-catalogue-product-classification.md`. Maestros
Family/Category/Tag operativos. Product Edit: General (name/status) +
Imágenes + Documentos. Form kit: `Select` simple (Popover), sin multi ni
creatable. E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** vínculos 0..1 / 0..N / 0..N; UI General; tags in situ +
`tags.create` → Active; tests/docs.

**Non-Goals:** Family↔Category; Create con clasificación; tab
Clasificación; filtros Index; deps npm nuevas si se puede con kit propio.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Schema
- **`products.family_id`** nullable FK → `families` (`nullOnDelete`).
  0..1 sin pivote. *Descartado:* pivote N:N usado como 1:1 (v1).
- **`category_product`** (o `product_category`): `product_id`,
  `category_id`; unique par; cascade on delete product/category soft
  handled via detach on soft-delete optional — MVP: pivote hard rows;
  soft-deleted masters no aparecen en picker Active.
- **`product_tag`**: igual patrón.
- Relaciones Eloquent: `Product::family()`, `categories()`, `tags()`.

### D2 — Solo Edit; Create intacto
Clasificación **solo** en Edit/General. Create permanece nombre+tipo.
*Sesgo brief:* no debatido → esta decisión.

### D3 — Opciones del picker
- Listar maestros de la company con status **Active** (+ los ya
  vinculados aunque Inactive, para no perder selección al editar).
- Categories: opciones aplanadas (label con indent/path corto).
- Validación: IDs deben ser de la CurrentCompany; reject ajenos.

### D4 — Update payload
`UpdateProduct` (transacción) acepta además:
- `family_id` nullable
- `category_ids` array of int
- `tag_ids` array of int  
Sync pivotes (`sync`). No muta `type`.

### D5 — Tags in situ
- Endpoint dedicado p. ej. `POST /products/tags/quick` (antes de
  `{product}`) o `POST /products/{product}/tags/quick-create`:
  body `{ name }`; authz `tags.create` + company; Action
  `QuickCreateTag` (o reusa CreateTag con force Active); responde
  `{ id, name, slug, status: active }`.
- UI: tras éxito, añade id a `tag_ids` del form; el attach definitivo
  va en el save del Product (o attach inmediato + refresh — **preferir
  create inmediato + añadir a form**, sync en save para no dejar huérfanos
  si el user cancela… *Trade-off:* create inmediato puede dejar tags
  huérfanos si no se guarda el producto. **Decisión:** create inmediato
  Active en maestro (reutilizable) + incluir en selección del form; no
  exigir attach hasta save. Huérfanos Active son aceptables (quedan en
  CRUD Tags).
- Sin `tags.create`: MultiSelect sin acción “crear”; API 403.

### D6 — UI kit
- Añadir **`Form/MultiSelect`** (multi, search local, chips) reutilizando
  Popover como `Select`; prop opcional `creatable` + `onCreate(name)` /
  `canCreate`.
- Family: `Select` existente (nullable / clear).
- Categories: `MultiSelect` sin creatable.
- Tags: `MultiSelect` creatable si `can.create_tags`.
- Sin deps nuevas (`libraries.md`).

### D7 — Authz
- Ver/guardar clasificación: `ProductPolicy::update` (`catalogue.update`).
- Quick-create tag: `TagPolicy::create` / `tags.create`.
- No Functionality nueva.

### D8 — Vinculaciones
| V | ¿Sí? | Nota |
|---|---|---|
| V-07..V-09 | No | |
| V-20 | No | pivotes sin audit propio |
| V-21 | Sí | rutas bajo `module:products` |
| R-TEN-05 | Sí | |
| E2E | N/A | |

### D9 — Docs
Ficha Catalog: clasificación operativa. module-map. Glosario si hace
falta (pivotes). Backlog Family↔Category.

## Risks / Trade-offs

- [Tags huérfanos tras create sin save] → Mitigación: quedan en maestro
  Active; aceptable.
- [Árbol categories en multi] → Labels indentados; sin tree picker rico.
- [Soft-delete Family con productos] → `nullOnDelete` / nullify
  `family_id` al soft-delete no automático; al borrar Family soft,
  productos pueden conservar FK a trashed — picker muestra vinculado;
  cleanup opcional fuera de alcance (nullify en DeleteFamily si trivial).

## Migration Plan

1. Docs.
2. Migraciones `family_id` + pivotes.
3. UpdateProduct + QuickCreateTag + requests/routes.
4. MultiSelect + ProductForm Edit + Edit props.
5. Tests.
Rollback: drop pivotes/column; quitar UI/endpoint.

## Open Questions

Ninguna bloqueante.
