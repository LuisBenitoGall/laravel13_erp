## Why

Los roles custom de empresa ya existen, pero la asignación a miembros sigue limitada a
`manager`|`guest` en Action y UI. Sin poder asignar un custom, el RBAC tenant no es
usable de punta a punta. Es el primer change del cierre de restos de Fase 0.

## What Changes

- Extender `AssignCompanyRole` para aceptar cualquier rol Spatie **local** de la company
  (no solo manager|guest); rechazar roles de otra company o de plataforma.
- Al reasignar, un único rol de empresa por usuario en esa company (quitar el/los roles
  locales previos de esa company e asignar el nuevo).
- Extender `CompanyMemberController` + `Members.tsx`: selector con todos los roles
  locales; detectar el rol actual aunque sea custom.
- Validación FormRequest: `role` ∈ nombres de roles con `company_id` = company.
- Tests Feature (custom, rechazo cross-company/plataforma, authz).
- **No** nueva pantalla; **no** invitar miembros; **no** Form kit ni Platform console.

## Capabilities

### New Capabilities

- `company-member-role-assignment`: asignación UI/Action de cualquier rol local de
  company a usuarios vinculados (un rol a la vez).

### Modified Capabilities

- `company-roles`: la asignación a vinculados deja de limitarse a manager|guest.

## Impact

- `AssignCompanyRole`, `AssignCompanyRoleRequest`, `CompanyMemberController`,
  `Members.tsx`, posiblemente `CompanyRoleGate` docblock.
- Ficha `docs/modules/Admin.md` (asignación entra en alcance).
- Tests Feature Partners/Admin relacionados con members/role.
