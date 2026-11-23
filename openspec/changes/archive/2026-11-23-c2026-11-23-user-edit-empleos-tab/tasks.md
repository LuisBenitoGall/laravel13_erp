# Tasks

## 1. Estándar (antes de código)

- [x] 1.1 Enmendar `docs/standards/kernel.md` (User/Company o Super Admin): excepción
  de lecturas **solo lectura** ancladas a User/hub cuando el change/spec lo
  declare; no = “SA ve todas”. Verificar diff legible y sin contradicción residual
  con “Cross-tenant = bug” sin matiz
- [x] 1.2 Añadir **R-TEN-10** (o matiz explícito a R-TEN-05) en
  `docs/standards/architecture.md` alineado al kernel; verificar cita cruzada
- [x] 1.3 Alinear `.cursor/rules/01-kernel.mdc` (y mención mínima en `CLAUDE.md` si
  copia la frase plana) con el matiz; verificar always-on coherente

## 2. Backend proyección Empleos

- [x] 2.1 Exponer `has_employment_periods` (o equivalente) en `UserEditPageData`
  con `Employee::withoutGlobalScope('company')->where('user_id', …)->exists()`;
  verificar prop en Profile y CompanyUsers edit
- [x] 2.2 Endpoint ServerTable (profile + company-users) de periodos del sujeto:
  sin scope company, eager company/workplace/contractType; authz = acceso ficha
  (no `employees.view`); resource con columnas del brief + `observations`;
  verificar JSON con periodos en dos companies y 403 sin acceso a ficha
- [x] 2.3 Tests Pest: tab flag true/false; table multi-company; sin
  `employees.view` OK si hay acceso ficha — verde

## 3. Frontend tab Empleos

- [x] 3.1 `Users/Edit.tsx`: tab `empleos` solo si `has_employment_periods`; panel
  ServerTable + modal RO observaciones si hay texto; i18n ES; verificar self y
  ajena (tab ausente sin periodos; modal solo con texto)
- [x] 3.2 Pint dirty + tests del change en verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
