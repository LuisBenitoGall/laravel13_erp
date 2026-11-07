# Proposal

## Why

Tras la revisión explore y el change 1 (`.view` = dashboard), quedan huecos de
authz y seguridad en roles: Index tenant aún exige manager; copy tenant genera
conflictos; Create plataforma puede colar `permissions`; materialización no
fuerza `universal=false`; copy plataforma no recorta al catálogo destino.

Fuente: `docs/prompts/c2026-11-06-roles-hardening.md` (estricto / rajatabla).

## What Changes

- Index/table company-roles: `company-roles.view` + perímetro; **sin** exigir
  rol `manager`.
- **Eliminar** copy tenant (ruta, Action, Request, UI, props, tests).
- Create plataforma: no aceptar/persistir `permissions` en el alta.
- Materialización: locales con `universal = false` explícito.
- Actions de mutación tenant: assert de perímetro independiente de
  `Gate::before`.
- Copy plataforma: recorte de permisos al catálogo tenant del destino.
- Respetar semántica change 1 (no revertir `.view` / `.viewAny`).

## Capabilities

### New Capabilities

- (ninguna capability nueva de producto; hardening de las existentes)

### Modified Capabilities

- `company-custom-roles`: Index sin manager; **REMOVED** copy tenant; Purpose
  sin copy; universales sin mención de copy.
- `platform-roles`: Create sin `permissions`; materialización
  `universal=false`; copy plataforma con recorte catálogo destino.

## Impact

- `CompanyRolePolicy`, `CompanyRoleController`, front `CompanyRoles/*`,
  Actions copy/create/materialize/toggle/update/delete company +
  `CopyPlatformRoleToCompanies` / `CreatePlatformRole` / FormRequests.
- Specs + tests Feature. Admin.md fila roles.
- E2E: no aplica.

## Proceso (no negociable)

10 → 20 → 25 → **parada Luis/explore** → solo entonces 30 archive.
Prohibido declarar “roles completos” sin OK de producto post-25.
