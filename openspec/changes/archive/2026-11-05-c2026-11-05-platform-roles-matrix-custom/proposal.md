# Proposal

## Why

La matriz del Edit de roles plataforma hardcodea las 5 acciones canónicas y
oculta permisos custom del catálogo (p. ej. `companies.limpiar-algunas-empresas`).
Los custom deben asignarse por la misma UI.

Fuente: `docs/prompts/c2026-11-05-platform-roles-matrix-custom.md`.

## What Changes

- `PlatformRolePermissionCatalog::forMatrix`: listar permisos Spatie del
  prefijo `{functionalitySlug}.*` (canónicos + custom); excluir
  `{module}.access` / sufijo `access` de la card Functionality.
- «Todas las acciones» / `TogglePlatformRoleFunctionality`: operar sobre el
  mismo set visible (no solo las 5).
- Toggle individual: sin cambio de contrato (ya acepta nombres Spatie); validar
  que custom atribuibles siguen el filtro AdminOnly existente.
- Spec `platform-roles`: matriz ampliada a custom.
- Change **aparte** de `platform-roles-edit` (ya archivado). Sin company-roles.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `platform-roles`: matriz Functionality = canónicos + custom; «Todas las
  acciones» incluye custom.

## Impact

- `PlatformRolePermissionCatalog`, `TogglePlatformRoleFunctionality`, tests
  matriz/toggle; ficha Admin.md breve.
- `CompanyRolePermissionCatalog`: fuera de alcance.
- E2E: no aplica.
