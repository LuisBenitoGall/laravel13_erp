# Tasks

## 1. Docs y registro de módulo (insumos ya en propose — verificar)

- [x] 1.1 Verificar ficha `docs/modules/HR.md`, glosario Empleo/ContractType,
  module-map ContractType, `capabilities-map.md` + enlace en README; ajustar
  si 20 detecta desvío vs specs
- [x] 1.2 Sembrar Module `rrhh` (Optional, Active) + Functionalities
  `contract-types` y `employees` + permisos Spatie + `rrhh.access`; verificar
  menú con capas+permiso (Feature o assert seeder)

## 2. ContractType — datos y backend

- [x] 2.1 Migration + model `ContractType` (`BelongsToCompany`, softDeletes,
  slug único por company, duration días); factory; verificar schema
- [x] 2.2 Policy + FormRequests (exists/unique tenant) + Actions CRUD +
  Controller + rutas `/hr/contract-types*` bajo `module:rrhh`; verificar
  authorize
- [x] 2.3 Activity log (V-20) en create/update/delete; verificar entrada log
  > Nota 20: implementado como `Auditable` (`created_by`/`updated_by`) según
  > design §7 y brief. `spatie/laravel-activitylog` (R-TRV-02, canon) NO está
  > instalado en el proyecto y ninguna entidad existente lo usa; no se añadió
  > dependencia. Si 10 quiere entradas de activity log reales: A) instalar el
  > paquete + `LogsActivity` en ContractType/Employee; B) mantener Auditable.
- [x] 2.4 Tests Pest: R-TEN-05, soft-delete no aparece en select alta,
  403 sin permiso — verde

## 3. ContractType — frontend

- [x] 3.1 Pages Index (ServerTable `*/table`) + Create/Edit form kit + i18n
  es/en/ca; verificar listado y alta manual o Feature Inertia

## 4. Employee — datos y backend

- [x] 4.1 Migration + enum status + model `Employee` (`BelongsToCompany`);
  factory; verificar schema y casts decimal
- [x] 4.2 Actions Create / Renew / Update / End (transacción ≤1 active;
  validar user_companies + FKs tenant); Policy + FormRequests + Controller
  + rutas `/hr/employees*`; verificar renovación cierra active
  > Notas 20 (detalle no fijado por design): cierre del `active` previo →
  > `end_date` = día anterior al inicio del nuevo (si falta; nunca antes de su
  > `start_date`); estado inicial: inicio futuro → `scheduled`, `end_date`
  > pasado → `ended`, resto `active`; Renovar/Finalizar solo sobre `active`
  > (422 `employee`); Policy `renew` = `employees.update`+`employees.create`.
- [x] 4.3 Activity log (V-20); verificar
  > Nota 20: ver 2.3 (Auditable; sin paquete activitylog).
- [x] 4.4 Tests Pest: membresía obligatoria, User inexistente en flujo no
  aplica, ≤1 active, renovación, R-TEN-05, 403 — verde

## 5. Employee — frontend

- [x] 5.1 Index ServerTable + Create (selector Users de `user_companies`) +
  Edit/historial + acción Renovar; i18n; verificar flujo renovar en UI

## 6. Acceso módulo (V-21)

- [x] 6.1 Feature plantilla templates-backend §13: módulo off → denegado;
  sin permiso → 403; capas+permiso → OK — verde

## 7. Cierre calidad

- [x] 7.1 Pint dirty + PHPStan + tests del change en verde
- [x] 7.2 Confirmar NonGoals no implementados (fichajes, cron vencimientos,
  create User)

## 8. Corrección indefinidos (duration null) — post-apply

- [x] 8.1 Specs/design: `duration` null = indefinido; end_date required si
  duration set — verificar artefactos alineados
- [x] 8.2 Backend: validación EmployeePeriodRules + helper `isIndefinite()`;
  options de create/renew exponen `duration`; tests Pest verde
- [x] 8.3 UI: ContractType switch indefinido; Employee end_date required +
  sugerencia start+duration; Index tipos muestra «Indefinido»; i18n es/en/ca

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
- Tras archive: alinear fila RRHH en `capabilities-map.md` con paths
  `openspec/specs/hr-*` definitivos.
