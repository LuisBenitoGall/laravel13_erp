# Design

## Context

See proposal.md — Why. Estado actual relevante:

- `CreateFunctionality` ya provisiona `{slug}.viewAny|view|create|update|delete` y
  `{module}.access` (R-AUT-07).
- Functionality `permissions` (módulo `admin`) ya está en `AdminCatalogSeeder`; menú
  Configuración ya lista el ítem; `MenuBuilder` hoy resuelve `permissions` →
  `admin.roles.index` (temporal).
- Modules / platform Roles usan Policy + Spatie `<functionality>.*` (no “solo SA”).
- v1 `/admin/permissions`: DataTable + modal Create (Functionality + sufijo) + destroy;
  vínculo por prefijo de name, sin FK; sin Edit.
- Spec `menu-core-pillars`: ítem temporal Permisos→Roles y *SHALL NOT* CRUD custom / ×7.

## Goals / Non-Goals

**Goals:**

- Index + Create modal + Delete Acciones sobre Spatie Permission (guard `web`).
- Autorización idéntica en espíritu a Modules: `PermissionPolicy` + `permissions.*`.
- Helper/contrato único para detectar canónico vs custom (Create reserved + Delete guard).
- Actualizar `MenuBuilder` + Purpose de `menu-core-pillars` + ficha `Admin.md`.

**Non-Goals:**

- Matriz Roles↔permisos; permisos directos a User; Edit de name; regenerar canónicos.
- Soft delete de Permission; FK `functionality_id` en `permissions`.
- E2E Pest browser (no es flujo de `e2e.md` §4).

## Decisions

### D1 — Gate: Functionality `permissions` + Policy (no “solo SA”)
**Elección:** Policy sobre el modelo Spatie Permission (o wrapper de dominio si el
proyecto ya tipa uno) comprobando `permissions.viewAny|create|delete` (y `view`/
`update` si el Policy mirror estándar lo exige aunque no haya UI Edit). Super Admin
sigue con `Gate::before`.
**Alternativa descartada:** Solo `hasRole('Super Admin')` en controller — rompe el
patrón Modules/Roles y deja muertos los Spatie `permissions.*` ya sembrables.
**Nota:** La Functionality ya existe en seeder; 20 no crea otra — solo asegura
provision/sync y menú.

### D2 — Vínculo Functionality = prefijo de name (sin FK)
**Elección:** Igual que v1 / R-AUT-02. Index resuelve label i18n de Functionality por
`explode('.', name)[0]` → slug. Create recibe `functionality_id` (o slug) y construye
el name en Action.
**Alternativa descartada:** Columna `functionality_id` en `permissions` — migración
Spatie + divergencia del paquete; fuera de alcance.

### D3 — Canónicos protegidos + sufijos reservados
**Elección:** Set reservado/protegido =
`viewAny|view|create|update|delete` como sufijo de cualquier Functionality, y cualquier
name que termine en `.access` (comodín de módulo). Create valida `not_in`; Delete Action
aborta con 403/AuthorizationException si es canónico.
**Alternativa descartada (brief A):** Delete libre como v1 — rompe menú/policies.

### D4 — Hard delete Spatie
**Elección:** `Permission::delete()` (cascada pivots Spatie). Sin SoftDeletes.
**Alternativa:** Soft delete — Spatie no lo modela; confunde `findOrCreate` del
auto-provision.

### D5 — UI: Index ServerTable + modal Create; sin página Edit
**Elección:** Calcar patrón Functionalities anidadas / v1: ActionBar «Nuevo permiso»,
modal (select Functionality + sufijo), Acciones Delete con confirm solo si `canDelete`.
Columnas mínimas: `name`, funcionalidad (label), Acciones. `exportable` a criterio de 20
(default false salvo necesidad).
**Alternativa descartada:** Página Create/Edit full — overkill vs v1 y brief.

### D6 — Capas Actions / FormRequests
**Elección:** `CreatePermission` / `DeletePermission` transaccionales; FormRequests
con authorize + rules (Functionality exists, sufijo slugable, unique name compuesto).
Controller delgado Inertia. Rutas bajo `admin/permissions` + `*/table` ServerTable.

### D7 — E2E
**Elección:** No aplica. Feature tests: Index authz, Create valid/reserved/duplicate,
Delete custom vs canónico, menú Permisos → Index.

## Risks / Trade-offs

- [Permisos huérfanos tras soft-delete de Functionality] → Mitigación: ya acordado no
  purge Spatie al soft-delete; el Index puede mostrar prefijo sin Functionality viva —
  mostrar slug crudo o “—”; no bloquear Delete custom.
- [Borrar custom asignado a Roles] → Mitigación aceptada: hard delete quita pivots;
  asignación es change futuro; UI confirm suficiente.
- [Sufijo libre vs slug] → Mitigación: normalizar sufijo con `Str::slug` (como v1) y
  validar unicidad del name final.

## Migration Plan

1. Desplegar rutas/UI/Policy; apuntar menú `permissions` al Index.
2. Sin migración BD (tabla Spatie existente).
3. Rollback: revertir `MenuBuilder` al enlace Roles; retirar rutas.

## Open Questions

Ninguna que bloquee specs/tasks. (Gate y hard delete cerrados en D1/D4.)
