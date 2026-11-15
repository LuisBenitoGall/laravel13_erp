<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-15-workplaces
Rule: 10-architecture
Summary: CRUD de `Workplace` (centros de trabajo) de la company en sesión,
  con dirección embebida (1 por centro) y menú Empresas → Centros de
  trabajo. Index ServerTable. MVP de campos.
Goals:
- Entidad `Workplace` (`BelongsToCompany` / siempre company de sesión).
- CRUD completo: Index `<ServerTable>`, Create, Edit, Delete.
- Menú bajo Module `empresas`: Centros de trabajo (Functionality).
- Dirección embebida en el workplace: `address`, `cp`, `town_id` (+
  cascada geografía); un workplace = una dirección.
- MVP campos: name + dirección + `featured` + status.
NonGoals:
- Tabla `Address` polimórfica / N direcciones por workplace.
- Logo, website, email, phone, description, nif, zone (campos v1 no MVP).
- Vínculo user↔workplace (`user_workplaces`).
- Cost centers / uso en pedidos de otras companies.
- Google Places / autocomplete mapas.
- Confundir con `Worksite` (Obra) ni slug `Workspace`.
Skills:
- openspec-propose
- new-module
- tenancy-rbac
- v1-reference
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore`. Referencia v1: modelo `Workplace`, dirección
  en la misma fila (`address`/`cp`/`town_id`).
- Decisión Luis: código `Workplace`; 1 dirección por centro; MVP de
  campos.
- Town/Province/Country ya existen en Admin (catálogo geografía).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Workplace de la company en sesión
El sistema SHALL persistir centros de trabajo (`Workplace`) vinculados
siempre a la company de sesión (`BelongsToCompany`). SHALL NOT usar el
nombre de código `Worksite` ni `Workspace` para esta entidad.

#### Scenario: Scope sesión
- **WHEN** un actor con company de sesión lista o crea workplaces
- **THEN** solo opera sobre workplaces de esa company

### Requirement: CRUD Workplace con ServerTable
El sistema SHALL exponer CRUD completo de Workplace: Index con
`<ServerTable>`, Create, Edit y Delete, con Policy y permisos Spatie de
la Functionality correspondiente.

#### Scenario: Index Tabulator
- **WHEN** el actor abre el Index de centros de trabajo
- **THEN** ve un listado `<ServerTable>` (no grid ad-hoc)

### Requirement: Dirección embebida MVP
Cada Workplace SHALL tener como máximo una dirección embebida
(`address`, `cp`, `town_id`, con cascada país/provincia/población del
catálogo geográfico). Campos MVP del change: name, dirección, `featured`
y status.

#### Scenario: Formulario MVP
- **WHEN** el actor crea o edita un workplace
- **THEN** puede informar name, dirección (address/cp/town), featured y
  status; no ve logo/web/email/phone/description/nif/zone

### Requirement: Menú Empresas → Centros de trabajo
El sistema SHALL añadir bajo Module `empresas` el ítem de menú Centros de
trabajo hacia el Index de workplaces.

#### Scenario: Entrada de menú
- **WHEN** un usuario con acceso al Module `empresas` y permiso de la
  Functionality obtiene el menú
- **THEN** ve Centros de trabajo bajo Empresas y puede abrir el Index


## MODIFIED Requirements

### Requirement: Menú Empresas con Centros de trabajo
(El pilar `empresas` con Mis empresas / Directorio.) SHALL añadir el
subítem Centros de trabajo sin sustituir las entradas existentes.

#### Scenario: Tres entradas (o más)
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Centros de trabajo como entrada distinta de Mis empresas
  y Directorio
