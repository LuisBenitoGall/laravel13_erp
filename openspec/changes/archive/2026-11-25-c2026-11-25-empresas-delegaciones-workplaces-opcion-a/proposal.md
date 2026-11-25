# Proposal

## Why

La UI de Edit Delegación mezcla dos nociones de “centro” (sedes de la matriz
vinculadas vs workplaces con dueño = hija). Eso confunde a usuarios no
avanzados. Se adopta **opción A**: todos los centros viven en la matriz; la
Delegación solo se asocia N:N. Hay que migrar datos, simplificar UX y corregir
el kernel que aún describe “Workplace de la Delegación” como dueño paralelo.

## What Changes

- Modelo: `company_id` de Workplace = siempre matriz; asociación solo vía
  `workplace_delegation` (N:N + preferida intactos).
- `CreateDelegation`: crear sede en la **matriz** + vincular (y/o vincular
  existentes); nunca `CreateWorkplace` con dueño = hija.
- Edit Delegación: un solo listado “Sus centros” (RO + enlace a Edit sede);
  eliminar CRUD `DelegationWorkplaceController` / panel hijo.
- Migración de workplaces con `company_id` = Delegación → matriz + pivote.
- Actualizar kernel / R-* / glosario / always-on a opción A (mantener
  invariantes aplazadas de documentos).
- Tests Pest de alta, Edit, migración, N:N desde Centros de trabajo.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `company-delegations`: alta y Edit alineados a opción A; sin CRUD de sedes
  hijas.
- `workplace-delegation-map`: pivote = única relación Delegación↔Workplace;
  lectura “Sus centros” desde Edit Delegación.
- `workplaces`: única vía de alta/edición de sedes del grupo (matriz).

## Impact

- Actions/controllers Delegación y Workplace; migration de datos; UI
  Create/Edit Delegación; standards.
- E2E §4: N/A.
