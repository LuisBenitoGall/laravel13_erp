# Proposal

## Why

El Create de roles de empresa aún mezcla matriz de permisos y no sigue el
patrón del Create plataforma (solo metadatos). El tenant no debe ver ni oír el
concepto «universal».

Fuente: `docs/prompts/c2026-11-06-company-roles-create.md`.

## What Changes

- Create tenant: nombre + descripción; sin matriz; sin UI/mención universal.
- POST crea rol `company_id` = sesión, `universal = false`; redirect Edit.
- Authz Create: `company-roles.create` (sin exigir además ser manager si hoy
  la Policy lo AND-ea).
- CTA Index → `/company/roles/create` correcto (verificar/corregir).
- Reutilizar patrón Create plataforma adaptado (campos sin universal).
- Edit/matriz/copy: fuera (change posterior).

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `company-custom-roles`: Create por metadatos; authz `company-roles.create`;
  redirect Edit; sin universal en UI.

## Impact

- `CompanyRoles/Create`, posible split de `CompanyRoleForm`,
  `RoleMetadataFields` (modo sin universal o props), Policy `create`,
  `create()` sin props matrix, tests Feature.
- E2E: no aplica.
