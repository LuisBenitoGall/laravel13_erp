# Design

## Context

Brief: `docs/prompts/c2026-11-05-platform-roles-edit.md`. Artefactos previos del
salto explore se reescriben aquí. v2 Edit = `RoleForm` `max-w-4xl` + PUT con
permisos. v1 = descripción + AJAX checkboxes + copy modal (no SA, no
universal). Stack v1 no se clona (R-FE-03: `patchJson` + toast sonner).

## Goals / Non-Goals

**Goals:** (del brief)

- Layout v1 ancho completo + organización permisos.
- Toggles ágiles (permiso / Functionality); revert + toast si falla.
- Auto `{module}.access` con ≥1 permiso bajo el Module.
- Guardar = descripción.
- Copy a companies del perímetro; versionar nombre si colisión.

**Non-Goals:**

- Alcance limitado a Edit **plataforma** (brief). No cablear company-roles Edit.
- No portar Livewire / `module_*` legacy / JSON menús v1.
- No cambiar sync manager local ni materialización de universales.
- E2E Pest browser: no aplica (`e2e.md` §4).
- Ficha `docs/modules/*` nueva: no aplica; actualizar fila Admin.md.

## Decisions

### D1 — Toggle HTTP

- Endpoint permiso: enable/disable un `permission` name Spatie.
- Endpoint Functionality: enable/disable las 5 acciones canónicas existentes
  para ese slug.
- Cliente: `patchJson`; UI optimista; error → revert + toast.

### D2 — `{module}.access`

- **Grant (brief):** al quedar ≥1 permiso de Functionality del Module M →
  asegurar `M.access`.
- **Revoke (decisión 10, coherencia AND):** al quedar 0 permisos de
  Functionalities de M → revocar `M.access`. Sin esto el comodín quedaría
  huérfano respecto a la activación descrita en el brief.
- Un permiso de Functionality no concede otras acciones ni otras funcs.

### D3 — Layout

Réplica estructural v1 en React: anclas Module, cabecera Module (color/estado
“tiene permisos”), grid cards Functionality colapsables con master checkbox +
acciones. Sin `max-w-4xl` como contenedor principal.

### D4 — Metadatos Edit

- Name: readonly en Edit (v1).
- Descripción: editable + Guardar (PUT).
- Universal: no editable en Edit (evita reclasificar tras alta; coherente con
  copy solo no-universal).
- `UpdatePlatformRole` deja de sincronizar matriz desde PUT.

### D5 — Copy

- CTA si `!universal`, no Super Admin, y el usuario en sesión está vinculado a
  **más de una** company Active (`forUser`).
- Destinos: companies Active del perímetro **excepto** `CurrentCompany` (sesión).
- Crea Role `company_id` = destino + copia permisos Spatie (team company).
- Nombre libre → mismo `name`; colisión → `"{name} (2)"`, `"(3)"`, … hasta
  libre en esa company.

### D6 — Spec / vinculaciones

Delta `platform-roles`. V-07/08/09: no. R-TEN-05: sí en copy (rol en A no es
dato de B). E2E N/A.

## Risks / Trade-offs

- [Toggles concurrentes] → last-write-wins o cola simple por Functionality.
- [manager `permissions_readonly`] → respetar flag; toggles disabled.

## Migration Plan

Solo código + tests. Rollback = revert change.

## Open Questions

Ninguna.
