# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-26-catalogue-product-crud.md`. Dominio Catalog aún
no existe en código. Module-map ya nombra Catalog (Product, precios,
familias…) como fase 1; este change solo materializa Product mínimo.
Referencia v1: modelos `Product*` / Livewire — portar idea de artículo
físico vs servicio y maestro por company, no el stack. E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Dominio Catalog; Module `catalogue` + Functionality `products`;
Product CRUD mínimo; rutas `/admin/catalogue*`; ficha Edit con tabs
(General); docs/tests.

**Non-Goals (design-level):** i18n del nombre; SKU; unidades/IVA; taxonomía;
precios/tarifas; proveedores; tabs stub; consumo Sales/Inventory; deps
nuevas.

## Decisions

### D1 — Dominio `Catalog` + Module SaaS `catalogue`
**Elección:** `App\Domains\Catalog` (capa negocio / maestros). Module slug
**`catalogue`** (grafía UK alineada a URL), level **`Basic`**, Active.
Functionality **`products`** con permisos `products.{view,viewAny,create,update,delete}`
+ comodín `catalogue.access`. Menú: ítem **Catálogo** → `/admin/catalogue`.
AuthorizeBasicModules alinea el módulo en altas de company (como otros Basic).
**Descartado:** Module Optional (exigiría Mis módulos para un maestro núcleo);
poner Product bajo Admin/`configuracion` (catálogo global SA ≠ tenant);
Functionality slug `catalogue` sin `products` (el módulo crecerá con más
Functionalities: families, rates…).

### D2 — Naming
| UI | Inglés | Tabla / valor |
|---|---|---|
| Producto | `Product` | `products` |
| Producto físico | `ProductType::Physical` | `physical` |
| Servicio | `ProductType::Service` | `service` |
| Activo / Inactivo | `ProductStatus` | `active` / `inactive` |
| Module | `catalogue` | slug menú/middleware |
| Functionality | `products` | permisos `products.*` |

Rutas HTTP: prefijo `admin/catalogue` (resource). Names:
`admin.catalogue.*` (index/create/store/edit/update/destroy/table).
Pages Inertia: `Catalog/Products/*`. Routes file: `routes/domains/catalog.php`
con middleware `auth` + `company` + `module:catalogue`.

### D3 — Product mínimo (modelo)
**Elección:**
- `company_id` + `BelongsToCompany`; índices `(company_id, …)`.
- `name` string (no JSON; V-12 no aplica este change).
- `type` enum string; **inmutable** tras create (UpdateRequest/Action
  no aceptan `type`; ignore/reject si viene en payload).
- `status` enum; Create **fuerza** `Inactive` en Action (ignorar input).
  Edit puede Active↔Inactive (`canTransitionTo` libre entre ambos).
- SoftDeletes + Auditable (`created_by`/`updated_by`) — playbook maestro.
- **Sin** unique `(company_id, name)` en MVP (homónimos permitidos; SKU
  futuro).
- Factory `for(company)` + states `physical`/`service`/`active`.

**Descartado:** unique name; tipo editable mientras Inactive; status en
Create form; hard delete.

### D4 — UI Create / Edit / Index
**Create:** form corto: `name` + `type` (select). Redirect a Edit tras store.
**Edit:** `RecordEditLayout` + un solo tab **General** (`id: info` o
`general`): `name` + `status`. `RecordSummary`: título = name; fields =
tipo (label i18n) + auditoría (`auditRecordSummaryFields`). Sin tabs stub.
**Index:** `<ServerTable>` columnas name, type, status (+ updated_at si
cabe el patrón de hermanos); acciones fila según permisos; destroy soft.

### D5 — Authz / menú / Realign
- Alta Module + Functionality en `RealignMenuCatalog` / seeder catálogo
  (mismo patrón `rrhh`/`empresas`).
- `MenuFunctionalityHref`: `products` → `/admin/catalogue` (slug inglés;
  evitar href `#`).
- SyncManagerPermissions / AuthorizeBasicModules: incluir `catalogue`.
- Policy `ProductPolicy`: tenancy + Spatie; SA no bypasea perímetro.

### D6 — Vinculaciones V-01..V-21
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí | `products.*` + `catalogue.access` |
| V-08 | Sí | Menú Catálogo |
| V-09 | Sí | Module `catalogue` Basic nuevo |
| V-10..V-19 | No | (V-12 traducciones = change futuro) |
| V-20 | Sí | Auditable Product |
| V-21 | Sí | Rutas bajo `module:catalogue` |
| R-TEN-05 | Sí | Product por company |
| E2E | N/A | Feature HTTP basta |

### D7 — Docs
Ficha `docs/modules/Catalog.md`; glosario ya tiene Product — añadir nota
Module `catalogue` si falta; module-map: Product operativo, resto pendiente;
capabilities-map: ejemplo Catalog oleada 1.

## Risks / Trade-offs

- [URL `/admin/*` con Module no-Admin] → Mitigación: patrón ya usado
  (empresas/usuarios bajo `admin` prefix); dominio PHP sigue Catalog.
- [Basic auto-on] → Mitigación: maestro comercial; si negocio pide Optional
  después, change de level (no este).
- [Sin unique name] → Mitigación: documentado; SKU/códigos en fase posterior.
- [Tipo inmutable] → Mitigación: RO en summary; tests que Update ignore/reject
  `type`.

## Migration Plan

1. Docs (ficha, glosario, module-map, capabilities-map).
2. Migración `products` + enums/model/factory.
3. Module/Functionality/menú/Realign + Policy/permisos.
4. Backend Actions/Requests/Controller/routes/table.
5. Frontend Index/Create/Edit + i18n.
6. Tests Pest.
Rollback: drop `products`, revert Module/Functionality/menú.

## Open Questions

Ninguna bloqueante.
