# Design

## Context

Brief: `docs/prompts/c2026-11-06-company-roles-edit.md`. Create tenant ya es
metadatos + redirect Edit. Edit company hoy = `CompanyRoleForm` + PUT con
matriz; catálogo hardcodea 5 acciones. Patrón canónico = Edit plataforma
(`c2026-11-05-platform-roles-edit` + matrix-custom).

## Goals / Non-Goals

**Goals:** del brief (espejo plataforma adaptado a catálogo tenant; copy;
bloqueo universales; name readonly; descripción de `manager` editable;
authz `company-roles.update`).

**Non-Goals:** Create; CRUD plataforma / materialización; sync manager local;
rediseño Index salvo ocultar Editar en universales; E2E browser N/A.

## Decisions

### D1 — Reutilizar patrón plataforma

- Endpoints tenant análogos: `PATCH company/roles/{role}/permissions`,
  `.../functionalities`, `POST .../copy`.
- Cliente: `patchJson` / modal copy como plataforma; UI optimista; error →
  revert + toast (R-FE-03).
- Layout: reusar componentes del Edit plataforma (anclas Module, cards
  Functionality) parametrizados al catálogo company; sin `max-w-4xl`.

### D2 — Catálogo + custom

- Extender `CompanyRolePermissionCatalog` al patrón
  `PlatformRolePermissionCatalog` (canónicos existentes + custom del slug),
  siempre `¬AdminOnly` y solo módulos autorizados ∩ activos.
- `filterPermissionNames` / toggles / copy usan el set filtrado (incl. custom
  + `{module}.access` vía lógica de toggles, no dentro de cards).

### D3 — Toggles y comodín

- Igual que plataforma: grant `{module}.access` con ≥1 permiso de Functionality
  del Module; revoke al quedar 0 (coherencia AND).
- Toggle Functionality = todos los permisos **listados** de esa card
  (canónicos + custom).
- `permissions_readonly` (`manager` local): toggles disabled + Actions
  rechazan mutación de permisos.

### D4 — Update metadatos

- PUT/PATCH update: **solo descripción**.
- Name siempre readonly en Edit (`name_readonly: true` en resource Edit).
- Descripción editable en `manager` / `guest` / custom (no universales).

### D5 — Copy

- CTA si rol `!universal`, usuario con `company-roles.update`, y ≥2 companies
  Active en perímetro (`forUser`).
- Destinos: Active del perímetro **excepto** CurrentCompany.
- Nuevo rol: `company_id` destino, `universal = false`, permisos =
  intersección origen ∩ catálogo destino.
- Colisión de `name` → `"{name} (2)"`, `(3)`, …
- R-TEN-05: rol creado pertenece solo al destino.

### D6 — Universales no editables (tenant)

- Index: sin control Editar si `universal` (flag `can_edit` / omitir action).
  No enseñar copy «universal» al usuario (sin labels del concepto).
- Policy `update` (y gates de toggle/copy): exigir rol de CurrentCompany,
  `!universal`, y `company-roles.update`. Roles plataforma (`company_id`
  NULL) ya fallan perímetro; reforzar rechazo explícito si `universal`.
- edit/update/toggle/copy: 403/404 coherente con resto del CRUD company.

### D7 — Spec / vinculaciones

- Delta `company-custom-roles`. Actualizar fila Admin.md.
- V-07/08/09: no nuevas. R-TEN-05: copy. E2E N/A.

## Risks / Trade-offs

- [Index hoy solo `forCompany`] → universales de plantilla no listan; el
  bloqueo cubre flag `universal` residual y URL directa.
- [Duplicar UI plataforma vs abstraer] → preferir reuso/params; no segundo
  design system.

## Migration Plan

Solo código + tests. Rollback = revert.

## Open Questions

Ninguna (brief cerrado).
