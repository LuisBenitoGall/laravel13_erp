# Design

## Context

Brief: `docs/prompts/c2026-11-23-user-edit-empleos-tab.md`.
Ficha `Users/Edit` compartida (Profile + CompanyUsers). Tab Empresas ya lista
`user_companies` sin scope de sesión. `Employee` usa `BelongsToCompany`; el
historial en HR Edit queda en company actual. Kernel/R-TEN-05/09 redactan
cross-tenant como bug sin matizar lecturas ancladas a User.

E2E §4 (`docs/standards/e2e.md`): **N/A** — no hay flujo catalogado para este
tab.

## Goals / Non-Goals

**Goals:** Tab Empleos RO multi-company; ocultar sin periodos; columnas y modal
acordados; enmienda canónica kernel/architecture; tests.

**Non-Goals:** Mutar periodos desde el tab; columna status; gate `employees.view`;
cambiar Index/Edit HR scoped.

## Decisions

1. **Orden de entrega: estándar → código**  
   Tasks primeras: enmendar `kernel.md` + `architecture.md` (nuevo **R-TEN-10**
   o matiz a R-TEN-05) y alinear `.cursor/rules/01-kernel.mdc` / `CLAUDE.md`
   mínimo si citan la frase plana. Así la UI no “excepciona” un estándar
   contradicho. (Ficha 10-architecture pide PR de estándar primero; aquí va
   **en el mismo change**, bloque 1, antes del tab — brief lo exige.)

2. **Texto canónico (borrador para 20)**  
   Mutaciones y listados de entidades de negocio: `CurrentCompany` /
   `BelongsToCompany`. **Excepción:** consultas de **solo lectura** ancladas a
   un User (u otro hub declarado en spec) que proyecten filas de varias
   companies del **sujeto**, cuando el change/spec lo declare. No es “SA ve
   todas las empresas” (R-TEN-09 intacto para bypass). R-TEN-05 sigue para
   CRUD/listados tenant-scoped; el test de aislamiento no prohíbe esta
   proyección documentada.

3. **Detección del tab**  
   `UserEditPageData` (o builder) expone `has_employment_periods: bool` vía
   `Employee::withoutGlobalScope('company')->where('user_id', …)->exists()`
   (soft-deleted fuera, comportamiento Eloquent por defecto). Edit.tsx añade
   tab `empleos` a la lista **solo si** esa flag es true (mismo patrón que
   password condicionado a `can.change_password`).

4. **Datos de tabla**  
   Endpoint JSON ServerTable análogo a `UserCompanyController::membershipTable`:
   rutas bajo profile y company-users (authz = ya poder ver/editar esa ficha;
   **no** `employees.view`). Query: `withoutGlobalScope('company')` +
   `user_id` del sujeto; eager load `company`, `workplace`, `contractType`.
   Resource con: `company_name`, `workplace_name`, `contract_type_name`,
   `job_title`, `start_date`, `end_date`, `weekly_hours`, `year_salary`,
   `observations` (para el modal; no columna). Sin `can.update/renew/...`.

5. **UI**  
   Panel `UserEmploymentPeriodsPanel` (nombre final inglés en código;
   label i18n «Empleos»). Columnas del brief. Acciones: icono → Dialog RO si
   `observations` no vacío; si vacío, sin acción de modal. Sin links a Edit HR
   obligatorios en este change.

6. **Permisos**  
   Self: autenticado dueño de la ficha. Ajena: misma authz que
   `CompanyUserController::edit` (`updateCompanyUser` / membresía). El endpoint
   table reutiliza esa puerta; no Spatie `employees.*`.

7. **Tests**  
   Feature: User con periodos en A y B → flag true + table JSON incluye ambas
   companies; User sin periodos → flag false; actor sin acceso a ficha ajena →
   403 al table; observations presentes/ausentes en payload.

## Risks / Trade-offs

- [Salario visible cross-company a quien edita la ficha] → Aceptado en explore
  (solo lectura; ancla User).
- [Confusión con R-TEN-05 en reviews] → Mitigation: R-TEN-10 explícito +
  comentario en Action/query “hub User readout”.

## Migration Plan

1. Standards. 2. Backend flag + table endpoint + resource. 3. Frontend tab +
panel + i18n. 4. Tests. Sin migration BD.

## Open Questions

Ninguna bloqueante del brief.

## Excepciones a standards

No es excepción improvisada: el change **corrige** el estándar (R-TEN-10 /
matiz kernel) y luego implementa la proyección ya permitida. Tras el bloque 1,
el código cumple standards actualizados.
