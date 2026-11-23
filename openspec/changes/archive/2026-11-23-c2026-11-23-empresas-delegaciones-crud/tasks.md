# Tasks

## 1. Catálogo menú y permisos

- [x] 1.1 Functionality `delegations` bajo module `empresas` (RealignMenuCatalog /
  seeder), labels i18n, permisos view/viewAny/create/update/delete; verificar
  ítem en aside y permisos en BD
- [x] 1.2 Entrada glosario Delegación (= Company hija, ≠ Workplace); verificar
  término en `docs/standards/glossary.md`

## 2. Backend Delegaciones

- [x] 2.1 Action `CreateDelegation`: parent_id = CurrentCompany (matriz),
  reutilizar roles/attach/sync **sin** AssignDefaultCompanyAccount; crear ≥1
  Workplace; verificar sin fila company_accounts y parent_id correcto
- [x] 2.2 Routes/controllers/policy Index-table/Create/Store/Edit/Update/Destroy
  acotados a hijas de sesión; rechazo si sesión no es matriz; verificar Pest
  listado solo hijas + 403 sesión hija
- [x] 2.3 Update/Delete Delegación (campos empresa; soft delete según Policy
  Company); verificar authz `delegations.*`

## 3. Switcher solo matrices

- [x] 3.1 Filtrar `available_companies` / select post-login a `parent_id` null;
  rechazar switch a hija; verificar topbar/select sin delegaciones y switch
  rechazado

## 4. Frontend

- [x] 4.1 Pages Index (ServerTable) + Create (empresa + workplace mínimo) + Edit
  Inertia; i18n; verificar flujos distintos de Mis empresas
- [x] 4.2 Pint dirty + tests del change en verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
