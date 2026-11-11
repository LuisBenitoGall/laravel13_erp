# Proposal

## Why

El tab Empresas de la ficha de usuario está vacío. Hace falta listar los
vínculos `user_companies` (membresía / acceso ERP, distinto del futuro
Empleo) en ServerTable y poder desvincular sin borrar User ni Company. El
pivot carece de posición y departamento.

## What Changes

- Migración: `position`, `department` varchar nullable en `user_companies`.
- Tab Empresas: ServerTable (patrón functionalities) con columnas nombre
  empresa, posición, departamento, fecha alta (`created_at` i18n); todas
  las memberships del user (incluida sesión).
- Acción eliminar = detach del vínculo únicamente; alta fuera de este tab.
- Authz: self o `company-users.update` + perímetro.
- Detach última company permitido; si el actor en sesión pierde su company
  de sesión → otra vinculada o logout.
- Specs: dejar explícito vínculo ≠ Empleo.
- Glosario / Admin / module-map si aplica.

## Capabilities

### New Capabilities

- `user-companies-membership`: listado y detach de vínculos User↔Company
  en la ficha (no Empleo).

### Modified Capabilities

- `user-edit`: tab Empresas operable (deja de estar vacío).

## Impact

- Pivot `user_companies`; relación User/Company withPivot.
- Endpoint `*/table` + destroy anidados en profile / company-users.
- Extender o reutilizar `DetachCompanyUser` + lógica de sesión/logout.
- Front: panel ServerTable en tab Empresas.
- Tests Feature (detach, última, switch sesión, logout, authz).
