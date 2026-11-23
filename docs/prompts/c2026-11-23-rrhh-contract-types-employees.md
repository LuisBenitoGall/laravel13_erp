<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-23-rrhh-contract-types-employees
Rule: 10-architecture
Summary: Arrancar el módulo RRHH (slug `rrhh`) con tipos de contrato laboral por
  empresa y registro de empleados (periodos laborales), corrigiendo el esquema v1
  para tenancy v2; este desarrollo establece además el mapa de capabilities como
  referencia para módulos posteriores.
Goals:
- Crear ficha `docs/modules/HR.md` (playbook) y proponer el change OpenSpec del
  módulo RRHH oleada 1.
- **ContractType** (`contract_types`): catálogo CRUD por company (tipos de
  contrato laboral). Campos base v1: name, slug, duration en **días**,
  with_holidays, status; soft-delete **sí** (el histórico de empleados puede
  seguir apuntando al tipo).
- **Employee**: periodo laboral de un User en una company (no confundir con
  `user_companies`). Incluir `company_id` + `BelongsToCompany` (corrección
  normativa vs v1, que solo deducía company vía contrato). Resto alineado a
  esquema v1: user_id, contract_type_id, workplace_id nullable, job_title,
  start_date, end_date, weekly_hours, time_control, year_salary, observations;
  status = enum string (`scheduled` / `active` / `ended`).
- Reglas de producto cerradas: (1) solo User que ya esté en `user_companies` de
  esa company; (2) naming `ContractType` / `contract_types`; (3) ≤1 empleo
  **activo** por (user, company) — renovar = cerrar el actual + crear periodo
  nuevo; (4) alta solo con User **existente** (no crear User+empleo en el
  mismo flujo).
- Module SaaS slug **`rrhh`** + labels i18n; Functionalities de oleada 1:
  tipos de contrato + empleados.
- Reutilizar `Workplace` (Admin), User/UserCompany, ServerTable, form kit;
  dominio HR según module-map.
- **Mapa de capabilities:** este change/desarrollo SHALL establecer el mapa
  (índice on-demand: qué capabilities/standards/skills cargar por tipo de
  trabajo) usando RRHH como **primer ejemplo de referencia** para módulos
  posteriores y para reducir coste de contexto (no cargar el catálogo entero
  de specs).
NonGoals:
- Fichajes, calendarios, planning, vehículos.
- Vencimientos/notificaciones programadas de contratos (visión; oleada
  posterior).
- Alta de User nueva desde el flujo de empleo.
- Empleado sin membresía `user_companies` en la company.
- Migrar/importar datos v1 (tabla vacía; Viuda Vila no usa la funcionalidad).
Skills:
- openspec-propose
- new-module
- tenancy-rbac
- laravel-best-practices
- v1-reference
Notes:
- Origen: `/opsx-explore` post always-on adelgazado; arranque RRHH + mapa de
  capabilities como referencia de coste/calidad.
- Referencia v1 (solo lectura): tablas `contracts` / `employees`; en v1
  `employees` es periodo (historial por user), company vía join a contracts;
  UI “contratos del empleado” / nuevo contrato. Local `admin_erp`: 0 filas
  (esperado).
- Glosario hoy: `Empleo` TBD vs `Employee`; alinear en el change (Employee =
  periodo laboral; ≠ UserCompany).
- Cross-domain: leer Workplace de Admin; no escribir Workplace desde HR.
- Ubicación exacta del fichero del mapa de capabilities: fijarla en design
  (p. ej. bajo `docs/standards/` o `docs/agents/`) si no hay canon previo;
  debe quedar usable por 10/20 en changes futuros.


## ADDED Requirements

### Requirement: Catálogo ContractType por empresa
El sistema SHALL permitir CRUD de tipos de contrato laboral
(`ContractType` / `contract_types`) acotados a la company de sesión, con
duration en días, soft-delete habilitado, y status usable en formularios de
empleo.

#### Scenario: Alta de tipo
- **WHEN** un actor autorizado crea un tipo de contrato con name, duration
  (días) y demás campos válidos
- **THEN** queda persistido para esa company y listable en el Index del
  catálogo

#### Scenario: Soft-delete con histórico
- **WHEN** se soft-deletea un tipo que tiene empleados históricos
- **THEN** el tipo deja de ofrecerse para altas nuevas y los periodos
  históricos siguen pudiendo referenciarlo

### Requirement: Empleados como periodos laborales de la company
El sistema SHALL registrar empleados de la company como periodos laborales
(`Employee`) con `company_id`, vinculados a un User y a un ContractType del
mismo tenant. SHALL NOT tratar `user_companies` como empleo.

#### Scenario: Alta solo con membresía
- **WHEN** un actor autorizado da de alta un periodo para un User
- **THEN** el User debe existir ya en `user_companies` de esa company; si no,
  se rechaza

#### Scenario: Un solo activo
- **WHEN** un User ya tiene un periodo `active` en la company y se renueva o
  se crea otro periodo activo
- **THEN** el periodo anterior queda cerrado (`ended`) y el nuevo queda como
  el activo (≤1 activo por user+company)

#### Scenario: Renovación
- **WHEN** un actor autorizado renueva el empleo de un User
- **THEN** se cierra el periodo activo y se crea un periodo nuevo (no basta
  con solo cambiar el tipo sobre la misma fila sin cerrar)

#### Scenario: Solo User existente
- **WHEN** se inicia el alta de empleo
- **THEN** se elige un User ya existente (no se crea User en ese flujo)

### Requirement: Módulo rrhh y menú
El sistema SHALL exponer el Module SaaS con slug `rrhh` y labels i18n, con
Functionalities de oleada 1 para tipos de contrato y empleados, conforme a
las tres capas y permisos `<functionality>.<acción>`.

#### Scenario: Acceso por permiso
- **WHEN** un actor sin el `.view` correspondiente (y sin Super Admin)
  fuerza el Index de tipos o de empleados
- **THEN** recibe 403

### Requirement: Mapa de capabilities de referencia
El desarrollo SHALL dejar publicado un mapa de capabilities (índice breve
on-demand) que indique, al menos para el patrón de un módulo nuevo como RRHH,
qué standards, specs/capabilities y skills cargar — de modo que changes
posteriores reutilicen ese mapa en lugar del catálogo completo de specs.

#### Scenario: Mapa usable
- **WHEN** un agente 10/20 arranca un change de módulo posterior
- **THEN** puede seguir el mapa para cargar solo el contexto pertinente
  (RRHH figura como ejemplo de referencia)

---

## MODIFIED Requirements

### Requirement: (glosario Empleo / Employee — si aplica en standards)
El glosario SHALL dejar de dejar `Empleo` como TBD: Employee = periodo laboral
RRHH; distinto de `user_companies`.

#### Scenario: Naming alineado
- **WHEN** se documente o implemente Employee en HR
- **THEN** no se confunde con membresía UserCompany ni con ContractType
