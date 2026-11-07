# Proposal

## Why

El Edit de roles de empresa sigue el formulario monolítico con matriz en
submit, desalineado del Edit plataforma (async, layout v1, copy, custom).
Además el tenant no debe poder editar roles universales (UI ni URL).

Fuente: `docs/prompts/c2026-11-06-company-roles-edit.md`.

## What Changes

- Edit tenant espejo plataforma: layout ancho, toggles async, Guardar =
  descripción, name siempre readonly, custom en matriz, comodín
  `{module}.access`, revert+toast.
- Matriz = autorizados ∩ activos ∩ ¬AdminOnly (+ custom Spatie del slug).
- CTA Copiar a otras companies del perímetro (>1); permisos recortados al
  catálogo destino; versionar `name` si colisión; destino siempre
  `universal = false`.
- Authz Edit/toggles/copy: `company-roles.update` + perímetro.
- Universales: sin CTA Editar en Index; Policy/rutas rechazan Edit, update,
  toggles y copy.
- Sin UI/mención «universal» en el flujo tenant.
- Create tenant no se reabre.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `company-custom-roles`: Edit async/layout/copy/custom; bloqueo universales;
  descripción editable en `manager` local; matriz con custom.

## Impact

- `CompanyRoleController` (+ toggles/copy), Actions, Policy/Resource,
  `CompanyRolePermissionCatalog`, pages `CompanyRoles/Edit` (+ componentes
  reutilizables del Edit plataforma si aplica), Index (ocultar Editar),
  tests Feature, fila `docs/modules/Admin.md`.
- E2E Pest browser: no aplica (`e2e.md` §4).
