# Proposal

## Why

Los listados ServerTable solo permiten acciones por fila. Falta un mecanismo
opt-in de selección + acciones masivas (eliminar con confirm, activar/desactivar,
y más adelante otras) reutilizable, empezando por Usuarios empresa.

## What Changes

- Extender `<ServerTable>` con prop opt-in de acciones masivas dinámicas:
  columna checkbox a la izquierda + desplegable en toolbar a la izquierda de
  «Mostrar/ocultar columnas».
- Selección limitada a la página actual; desplegable deshabilitado sin
  selección.
- Documentar el contrato en `server-table-reference` / `frontend.md` (incl.
  que al pedir mass actions en un Index el agente pregunte qué acciones).
- Piloto Usuarios empresa: bulk eliminar (detach vínculo sesión + confirm),
  activar y desactivar status, vía Actions + Policy del dominio.
- Endpoints/FormRequests bulk dedicados (no endpoint genérico cross-entity).

## Capabilities

### New Capabilities
- _(ninguna)_ — el comportamiento durable vive en capacidades existentes.

### Modified Capabilities
- `server-table-reference`: contrato opt-in de acciones masivas (checkbox,
  toolbar, alcance página, API dinámica).
- `company-users-index`: Index Usuarios empresa expone bulk eliminar /
  activar / desactivar sobre la selección de página.

## Impact

- Frontend: `Components/Table/ServerTable.tsx`, tipados, i18n, Index
  `Admin/CompanyUsers`; `docs/standards/frontend.md` (+ templates si aplica).
- Backend: Actions bulk (reutilizando `DetachCompanyUser` /
  `UpdateCompanyUserStatus`), controller routes, FormRequests, Policy checks
  por id / vínculo a company de sesión.
- Tests feature: aislamiento tenant en bulk; authz; smoke UI contract.
- Sin módulos/funcionalidades nuevas; sin dependencias Composer/npm nuevas.
- E2E Pest browser: no aplica (no es flujo crítico §4 de `e2e.md`; cubierto
  por feature tests).
