# Proposal

## Why

El tab Empresas lista y detacha vínculos, pero la edición de
`position`/`department` (Pencil + modal) se retiró en el strip HIGH de
`user-companies-tab` por quedar fuera de aquel contrato. Hace falta un
**change nuevo** que autorice y restaure ese write path, sin reabrir
listado/detach/sesión.

## What Changes

- Contrato OpenSpec: edición de `position` y `department` (nullable) del
  vínculo `user_companies` desde el tab Empresas.
- Restaurar el mismo patrón pre-strip: botón Editar (Pencil), modal
  Dialog, Action/Request/Policy `update`, rutas profile +
  company-users, `updateUrl`, tests, i18n, fila Admin.md.
- Authz: misma puerta que gestionar la ficha (self o
  `company-users.update` + perímetro).
- Listado ServerTable + detach + reglas de sesión: sin cambios de
  alcance.

## Capabilities

### New Capabilities

- _(ninguna)_ — el comportamiento durable sigue en
  `user-companies-membership`.

### Modified Capabilities

- `user-companies-membership`: añadir edición UI/API de
  position/department; el tab Empresas deja de ser solo lectura en esos
  campos.

## Impact

- Reintroducir `UpdateUserCompany`, FormRequest, `Policy::update`,
  métodos update del controller, rutas update.
- `UserCompaniesPanel` + props `updateUrl` en `Users/Edit`.
- Feature tests de update; ajustar test UI que hoy afirma ausencia de
  edit.
- i18n `editar_vinculo_empresa` / `vinculo_empresa_actualizado`;
  `docs/modules/Admin.md`.
