# Proposal

## Why

La ficha Edit de User no muestra el historial laboral (`Employee`) del sujeto.
RRHH ya persiste periodos por company; hace falta una proyección de **solo
lectura** en Perfil (self) y Usuarios empresa (ajena) con **todos** los
contratos del User, sin filtrar por empresa en sesión. El kernel hoy trata
cross-tenant como bug genérico; hay que matizar la excepción de lecturas
ancladas a User antes de codificarla.

## What Changes

- Tab **Empleos** en `Users/Edit` (self y company-users), visible solo si el
  User tiene ≥1 periodo `Employee`.
- Tabla de solo lectura: empresa, centro de trabajo, tipo de contrato, puesto,
  fechas, horas semanales, salario; acciones = modal de observaciones si hay
  texto. Sin editar/renovar/cancelar desde el tab. Sin columna de estado.
- Acceso al tab = acceso a la ficha (no exige `employees.view`).
- Enmienda `docs/standards/kernel.md` + `architecture.md` (R-TEN-*): lecturas
  cross-company de solo lectura ancladas a User (u hub explícito) cuando el
  change/spec lo declare; no es bypass SA ni mutación cross-tenant.
- Tests Pest de presencia/ocultación del tab y listado multi-company.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `user-edit`: tab Empleos condicional; misma ficha self/ajena.
- `hr-employees`: proyección de lectura de periodos del User sin scope de
  CurrentCompany en la ficha Edit (solo lectura; sin mutaciones).

## Impact

- `UserEditPageData`, `Users/Edit.tsx`, nuevo panel + endpoint `*/table` (o
  equivalente) de periodos por `user_id` con `withoutGlobalScope('company')`.
- Resource/DTO con `company_name` (+ workplace, contract type, etc.).
- `docs/standards/kernel.md`, `docs/standards/architecture.md` (y adaptador
  Cursor `01-kernel` si hace falta alinear el always-on).
- Sin migration BD. E2E §4: N/A (no hay flujo e2e.md para este tab).
