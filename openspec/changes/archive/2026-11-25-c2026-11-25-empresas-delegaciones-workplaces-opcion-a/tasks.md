# Tasks

## 1. Estándar opción A

- [x] 1.1 Actualizar `kernel.md` (quitar “Workplace de la Delegación” como
  dueño; Workplace = matriz + N:N); architecture/glosario/01-kernel si aplica;
  verificar constitución alineada a A y docs comerciales intactos

## 2. Backend y datos

- [x] 2.1 `CreateDelegation`: CreateWorkplace con company = matriz + pivote
  (preferida); soporte vincular `workplace_ids` existentes; ≥1 vínculo;
  verificar Pest alta
- [x] 2.2 Eliminar CRUD DelegationWorkplace (rutas/controller/request/panel);
  Edit solo listado “Sus centros” RO + enlaces; verificar 404 rutas viejas y
  UI un solo bloque
- [x] 2.3 Migración datos: workplaces con company_id = delegación → matriz +
  pivote; regla featured; verificar cero dueños-hija no soft-deleted

## 3. UI Create y cierre

- [x] 3.1 Create Delegación: copy llano; centro nuevo y/o elegir existentes;
  i18n sin jerga N:M; verificar flujo
- [x] 3.2 Pint dirty + tests del change (incl. N:N desde Edit Workplace) en
  verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
