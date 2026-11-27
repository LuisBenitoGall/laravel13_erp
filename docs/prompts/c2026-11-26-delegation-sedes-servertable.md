<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-26-delegation-sedes-servertable
Rule: 10-architecture
Summary: Sustituir el listado «Sus centros» del Edit Delegación por
  `<ServerTable>` (estándar Tabulator) con columnas de ubicación y fecha, e
  introducir sede preferida **por Delegación** (≤1, B2) editable desde esa
  ficha, sin tocar la preferida por sede del mapa actual.
Goals:
- Reemplazar `MatrixWorkplacesLinkedPanel` (lista HTML) por `<ServerTable>`
  server-side de workplaces de la matriz vinculados a la Delegación (RO del
  vínculo N:N salvo la nueva preferida).
- Columnas: nombre (enlace a Edit sede), código postal, población, provincia,
  país, fecha de creación, **sede preferida de la Delegación** (nueva).
- **B2:** añadir concepto «sede preferida de la Delegación» (como máximo una
  por Delegación, opcionalidad la fija 10). **Conservar** `is_preferred` actual
  = delegación preferida **por Workplace** (kernel + Edit Workplace intactos).
- Marcar/cambiar la sede preferida de la Delegación desde el Edit Delegación
  (ServerTable / edición de esa ficha).
- En esta tabla SHALL NOT mostrar la columna del mapa actual («esta sede
  prefiere esta delegación»); solo la nueva sede preferida.
- Tests Pest: table endpoint, ≤1 preferida por Delegación, preferida-por-sede
  intacta, perímetro matriz.
NonGoals:
- Sustituir o reinterpretar `is_preferred` del pivote (B1 descartada).
- Cambiar kernel § grupo / invariantes docs (default = preferida **de la sede**).
- Reabrir CRUD de centros en Delegación; escritura del vínculo N:N sigue en
  Edit Workplace.
- Cambiar `Workplace.featured` (Principal de matriz).
- Company currencies / FX.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` sobre captura Edit Delegación «Centros de trabajo»
  (lista + todos con badge «Delegación preferida»). Luis: ServerTable +
  columnas geo/fecha; opción B → aclarado **B2** (añadir, no sustituir);
  preferida de Delegación editable en Edit; tabla solo columna nueva
  (2026-11-26).
- Semántica actual (intacta): `workplace_delegation.is_preferred` = preferida
  **por sede** (≤1 Delegación por Workplace). Varias sedes pueden preferir la
  misma Delegación; por eso el badge masivo puede ser datos coherentes bajo A.
- Naming columna pivote / copy i18n («Sede preferida») lo fija 10; no reutilizar
  el label `delegacion_preferida` para el eje nuevo.
- Endpoint table bajo Delegaciones; authz `delegations.*` / update + perímetro.
- E2E §4: 10 declara si aplica.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: ServerTable Sus centros en Edit Delegación
El tab Centros de trabajo del Edit Delegación SHALL listar los workplaces
vinculados con `<ServerTable>` (no lista HTML). Columnas SHALL incluir nombre,
código postal, población, provincia, país, fecha de creación y sede preferida
de la Delegación. SHALL NOT incluir la columna de preferida-por-sede del mapa.

#### Scenario: Columnas visibles
- **WHEN** un actor autorizado abre Edit Delegación → Centros de trabajo con
  sedes vinculadas
- **THEN** ve ServerTable con CP, población, provincia, país, creación y sede
  preferida de la Delegación

### Requirement: Sede preferida por Delegación (B2)
El sistema SHALL permitir como máximo una sede marcada como preferida **de la
Delegación** entre los workplaces vinculados a ella. SHALL conservar la
preferida por Workplace (`is_preferred` actual) sin cambiar su semántica.
La sede preferida de la Delegación SHALL poder marcarse desde el Edit
Delegación.

#### Scenario: Como máximo una preferida por Delegación
- **WHEN** el actor marca la sede W2 como preferida de la Delegación D que ya
  tenía W1 preferida
- **THEN** solo W2 queda como sede preferida de D

#### Scenario: Preferida por sede intacta
- **WHEN** se cambia la sede preferida de la Delegación
- **THEN** las marcas `is_preferred` (Delegación preferida por cada Workplace)
  no se reinterpretan ni se borran por ese cambio


## MODIFIED Requirements

### Requirement: Lectura inversa del mapeo desde Edit Delegación (workplace-delegation-map)
La proyección RO de sedes en Edit Delegación SHALL presentarse como
`<ServerTable>` con las columnas de ubicación/fecha acordadas y la sede
preferida de la Delegación. La escritura del vínculo N:N SHALL seguir en Edit
Workplace.

#### Scenario: Datos coherentes con el pivote
- **WHEN** existe fila `workplace_delegation` (workplace de matriz A, company =
  Delegación D) y se abre el Edit de D en sesión A
- **THEN** esa sede figura en la ServerTable de D
