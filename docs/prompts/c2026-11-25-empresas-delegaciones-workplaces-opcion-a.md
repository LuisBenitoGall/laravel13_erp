<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-25-empresas-delegaciones-workplaces-opcion-a
Rule: 10-architecture
Summary: Simplificar Delegaciones ↔ Centros de trabajo a la opción A: todos los
  workplaces viven en la matriz; las delegaciones solo se asocian (N:N) vía el
  mapa existente; quitar el CRUD de centros “propiedad de la hija” y alinear
  kernel/UI al lenguaje de usuario medio.
Goals:
- **Modelo opción A (cerrado en explore):** un Centro de trabajo pertenece
  siempre a la Company matriz (`company_id` = matriz de sesión). Una Delegación
  (Company hija, NIF, centro de facturación) **no** es dueña de workplaces.
  Relación Delegación ↔ Workplace = **N:N** solo mediante el pivote
  `workplace_delegation` (ya existente): un workplace puede vincularse a varias
  delegaciones; una delegación puede tener 1..N workplaces asociados.
- **Único sitio de alta/edición de sedes:** menú Centros de trabajo (matriz).
  Ahí se gestiona el N:N (checks de delegaciones + preferida), como hoy en Edit
  Workplace.
- **Edit Delegación — pestaña/centros:** un solo bloque “Sus centros” =
  lista de workplaces de la **matriz** asociados a esa delegación (lectura +
  enlace a editar la sede en Centros de trabajo). SHALL NOT mostrar dos listas
  (matriz vinculadas RO + CRUD de centros de la hija). SHALL NOT usar jerga
  “mapa N:M” en copy de usuario; lenguaje llano.
- **Quitar** el CRUD de workplaces con `company_id` = Delegación
  (`DelegationWorkplacesPanel` y rutas asociadas de create/update/delete de
  workplaces bajo `/admin/delegations/.../workplaces`), salvo lo mínimo que
  10-architecture deje para migración/compat.
- **Alta de Delegación (≥1 centro):** el formulario de alta SHALL seguir
  pudiendo informar datos del primer centro, pero el Workplace se crea con
  `company_id` = **matriz** y se **vincula** a la nueva Delegación en el pivote
  (preferida = esa, si es el primero). Alternativa complementaria permitida en
  el mismo alta: elegir uno o más workplaces **ya existentes** de la matriz y
  vincularlos (debe quedar ≥1 vínculo al terminar el alta). No crear workplace
  con dueño = hija.
- **Datos ya existentes:** los workplaces cuyo `company_id` es una Delegación
  SHALL migrarse a la matriz (`company_id` = `parent_id` de esa Delegación) y
  quedar vinculados a ella en el pivote (si el vínculo no existía). Sin pérdida
  de filas; design detalla colisiones de `featured`/nombres.
- **Kernel / architecture / glosario / always-on:** corregir la constitución
  escrita en el change `empresas-delegaciones-kernel-polish` (archivado) donde
  distingue “Workplace de la Delegación” como dueño distinto: pasar a opción A.
  Mantener las invariantes aplazadas de documentos comerciales (dropdown
  sociedad, etc.) ya documentadas; solo ajustar la parte sedes/dueño.
- Tests Pest: alta delegación crea/vincula sede en matriz; Edit Delegación sin
  CRUD hijo; migración de sedes hijas; N:N intacto desde Edit Workplace.
NonGoals:
- Pedidos/presupuestos/albaranes/facturas (dropdown sociedad).
- Almacenes, productos, partners.
- Cambiar el significado de Delegación (sigue siendo Company hija, sin
  `company_accounts`, fuera del switcher).
- Eliminar el pivote N:N ni la preferida (se reutilizan).
- Opción B (centros con dueño = hija + mapa a la vez).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
Notes:
- Origen: `/opsx-explore` — UI Edit Delegación confusa (dos tipos de “centros”
  en la misma pestaña); usuario elige **opción A sin duda**.
- Archivados previos: delegaciones-crud, workplace-delegation-map,
  delegaciones-kernel-polish. Este change **corrige** el modelo de sedes hacia
  A y simplifica UX; no reabre el debate de sesión/switcher/cuentas.
- Decisión de alta (matriz + vínculo, no dueño hija): propuesta explore
  aceptada al pedir el brief (“ok”); si 10 ve riesgo, documentar en design
  sin volver a B.
- Misión explore: **solo este brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Workplaces solo de la matriz; Delegación por asociación N:N
Todo Workplace operativo del grupo SHALL tener `company_id` igual a la matriz.
Una Delegación SHALL asociarse a workplaces únicamente vía el pivote N:N
(`workplace_delegation`). SHALL NOT persistir nuevos workplaces con
`company_id` = Delegación.

#### Scenario: Alta delegación con centro nuevo
- **WHEN** un actor crea una Delegación e informa datos de un centro nuevo
- **THEN** el Workplace se crea con `company_id` = matriz y queda vinculado a
  la Delegación en el pivote (≥1 vínculo)

#### Scenario: Alta delegación vinculando sedes existentes
- **WHEN** un actor crea una Delegación eligiendo workplaces ya existentes de
  la matriz
- **THEN** quedan vinculados en el pivote y no se crea workplace con dueño =
  hija

#### Scenario: Un workplace varias delegaciones
- **WHEN** desde Centros de trabajo se vincula un workplace de la matriz a D1
  y D2
- **THEN** ambas asociaciones persisten (N:N intacto)

### Requirement: Edit Delegación — un solo listado “Sus centros”
El Edit de Delegación SHALL mostrar un único listado de workplaces de la matriz
asociados a esa Delegación (RO respecto al mapeo; enlace a Edit de la sede).
SHALL NOT ofrecer CRUD de centros con dueño = hija ni un segundo bloque
“sedes de la matriz” paralelo con otra semántica. Copy en lenguaje llano.

#### Scenario: Solo sus centros asociados
- **WHEN** el actor abre el Edit de una Delegación con sedes asociadas
- **THEN** ve esas sedes en un único listado comprensible, sin dos categorías
  técnicas de “centros”

#### Scenario: Sin centros asociados
- **WHEN** la Delegación no tiene vínculos
- **THEN** empty state + vía clara para vincular/crear sede en el flujo de
  matriz (sin crear dueño = hija)

### Requirement: Migración de workplaces con dueño = Delegación
Los workplaces existentes con `company_id` = Delegación SHALL pasar a
`company_id` = matriz padre y vincularse a esa Delegación en el pivote.

#### Scenario: Tras migrar
- **WHEN** se aplica la migración de datos
- **THEN** no quedan workplaces con `company_id` de una Delegación (salvo
  soft-deleted si el design lo exceptúa) y las asociaciones N:N reflejan la
  relación anterior

---

## MODIFIED Requirements

### Requirement: Constitución kernel / mapa sede–delegación
El kernel, architecture, glosario y adaptadores always-on SHALL describir
opción A: Workplace pertenece a la matriz; Delegación se asocia N:N; sin
“Workplace de la Delegación” como dueño paralelo. Las invariantes aplazadas de
documentos comerciales ya documentadas se mantienen (solo ajustar sedes).

#### Scenario: Kernel alineado a A
- **WHEN** un agente lee el kernel tras este change
- **THEN** no se exige ni se describe un CRUD de centros propiedad de la hija
  como modelo canónico

### Requirement: Alta Delegación y panel de centros (company-delegations)
El alta y la ficha de Delegación SHALL cumplir los requisitos de opción A
anteriores (centro en matriz + vínculo; Edit con un solo listado).

#### Scenario: Ya no hay panel CRUD de centros de la hija
- **WHEN** un actor abre el Edit de Delegación
- **THEN** no puede crear/editar/borrar workplaces con `company_id` = esa
  Delegación desde esa ficha
