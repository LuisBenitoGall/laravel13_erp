<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-12-mis-empresas-table
Rule: 10-architecture
Summary: Alinear el Index Mis Empresas (`Partners/Companies`) al contrato
  ServerTable canónico (ColumnDef, filtros, persistKey, exportable, Acciones),
  con fecha de alta `companies.created_at`, toggle de status y acciones Edit /
  Delete / Autorizaciones (SA).
Goals:
- Index Mis Empresas = listado de entidad **Company** (scope `forUser` /
  perímetro vínculo); no confundir con pivot `user_companies` de la ficha
  User.
- Traer la tabla al estándar `frontend.md` §3.2 + `server-table-reference`:
  `ColumnDef`, `headerFilterKind`, `persistKey`, `exportable`, columna
  Acciones, i18n, sin columna `slug`.
- Columna **Fecha de alta** = `companies.created_at` con maquetación date
  (`valueKind` / `headerFilterKind: 'date'`, `fmt.date`, alineación
  derecha según canon).
- Columnas de datos al menos: nombre, nombre comercial, nif, fecha alta
  (sin columna Estado suelta).
- Acciones: **toggle status** (primero) + **Edit** (ficha) + **Delete**
  (confirm, último entre destructivas) + enlace **Autorizaciones** para
  Super Admin / operator (hoy columna HTML; pasa a Acciones). Luis puede
  retirar Autorizaciones en un change posterior.
- Backend: exponer `created_at` en resource/table; whitelist sort/filter
  acorde (incl. fecha); endpoint table ya existente bajo Policy viewAny.
NonGoals:
- Relación User↔Company / tab Empresas de la ficha User.
- Empleo / RRHH.
- Rediseñar Create/Edit de Company salvo lo necesario para el Index.
- Quitar ya el enlace Autorizaciones (queda en Acciones; decisión de
  mantenimiento = más tarde).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (ColumnDef/fecha; toggle; edit/delete;
  autorizaciones SA; perímetro forUser; export/persist si testeable)
Notes:
- Origen: `/opsx-explore` módulo empresa → Mis Empresas.
- Estado: `Partners/Companies/Index.tsx` usa `ColumnDefinition` crudo; sin
  fecha; columna Autorizaciones HTML si `operator`; Resource sin
  `created_at`; `CompanyTableRequest` ya lista `created_at` en sortable.
- Referencia UI: Admin Modules Index + contrato ServerTable.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Mis Empresas ServerTable canónico
El Index Mis Empresas SHALL listar Companies del perímetro del actor con
`ServerTable` conforme al contrato de referencia (ColumnDef, header
filters, persistKey, exportable, Acciones). SHALL incluir fecha de alta
(`companies.created_at`) con formato/alineación de columna date. SHALL NOT
mostrar columna `slug` ni columna Estado suelta (el estado va en toggle).

#### Scenario: Columnas y fecha
- **WHEN** el actor abre Mis Empresas
- **THEN** ve nombre, nombre comercial, nif y fecha de alta (created_at)
  maquetada como date; sin slug ni columna Estado

#### Scenario: Contrato ServerTable
- **WHEN** se revisa el Index
- **THEN** usa ColumnDef + filtros de cabecera + persistKey + exportable +
  columna Acciones

### Requirement: Acciones Mis Empresas
La columna Acciones SHALL ofrecer toggle de status, Edit a la ficha,
Delete con confirmación, y para Super Admin/operator el acceso a
Autorizaciones de módulos de esa company (antes columna suelta).

#### Scenario: Toggle status
- **WHEN** el actor autorizado conmuta el status de una company
- **THEN** el estado se persiste y la fila refleja el cambio sin recarga
  completa (patrón ServerTable)

#### Scenario: Edit y Delete
- **WHEN** el actor autorizado usa Edit o Delete
- **THEN** Edit navega a la ficha de la company; Delete elimina con
  confirmación según Policy/perímetro

#### Scenario: Autorizaciones en Acciones (SA)
- **WHEN** un Super Admin/operator ve Acciones de una fila
- **THEN** puede abrir Autorizaciones de esa company desde Acciones (no
  como columna de datos HTML suelta)


## MODIFIED Requirements

### Requirement: Listado de companies respeta vínculo
(El escenario/comportamiento de listado en `company-tenancy`.) El listado /
table de Mis Empresas SHALL seguir devolviendo solo companies del
`user_companies` del actor, ahora con el Index ServerTable canónico y
fecha de alta expuesta.

#### Scenario: Listado de companies respeta vínculo
- **WHEN** un Super Admin (u otro usuario) solicita el listado/table de
  companies
- **THEN** solo obtiene companies presentes en su `user_companies`
