## Purpose

Centros de trabajo (`Workplace`) de la company en sesión: CRUD con
ServerTable, dirección embebida (una por centro) y menú bajo Empresas.
Base operativa distinta de Obra (`Worksite`) y del Directorio de companies.
En grupo societario (opción A), las sedes operativas viven en la matriz.

## Requirements

### Requirement: Centros de trabajo del grupo viven en la matriz
En el modelo de grupo societario (opción A), los workplaces operativos SHALL
pertenecer a la Company matriz. El menú Centros de trabajo (sesión matriz)
SHALL ser el único CRUD de alta/edición de esas sedes, incluida la gestión del
vínculo N:N a Delegaciones.

#### Scenario: CRUD solo en Centros de trabajo
- **WHEN** un actor gestiona sedes del grupo
- **THEN** lo hace bajo `/admin/workplaces` (matriz), no creando workplaces
  con dueño = Delegación

### Requirement: Workplace de la company en sesión
El sistema SHALL persistir centros de trabajo (`Workplace`) con
`BelongsToCompany`, acotados a la company de sesión. SHALL NOT nombrar
esta entidad `Worksite` ni `Workspace` en código.

#### Scenario: Scope sesión
- **WHEN** un actor con company de sesión lista o crea workplaces
- **THEN** solo opera sobre workplaces de esa company

#### Scenario: Aislamiento cruzado
- **WHEN** un actor intenta ver o mutar un workplace de otra company
- **THEN** la operación falla (404/403); no filtra datos ajenos

### Requirement: CRUD Workplace con ServerTable
El sistema SHALL exponer CRUD completo de Workplace bajo
`/admin/workplaces`: Index con `<ServerTable>`, Create, Edit y Delete
(soft), con Policy y permisos Spatie `workplaces.*`. El Edit (y Create si
aplica) de un workplace de la matriz SHALL permitir gestionar el mapeo N:M a
Delegaciones hijas y la delegación preferida, sin alterar el resto del MVP.

#### Scenario: Index Tabulator
- **WHEN** el actor con `workplaces.view` abre el Index
- **THEN** ve un listado `<ServerTable>` de workplaces de la sesión

#### Scenario: Create Edit Delete
- **WHEN** el actor autorizado crea, edita o elimina un workplace
- **THEN** la mutación aplica solo al registro de su company de sesión

#### Scenario: Gestión de vínculos en ficha sede
- **WHEN** el actor autorizado edita un workplace de la matriz
- **THEN** puede añadir/quitar delegaciones vinculadas y la preferida

### Requirement: Dirección embebida MVP
Cada Workplace SHALL tener como máximo una dirección embebida
(`address`, `cp`, `town_id`). El formulario SHALL ofrecer cascada
país → provincia → población del catálogo geográfico y persistir
`town_id` (no country/province en tabla). Campos MVP: name, dirección,
`featured` y status. SHALL NOT exponer logo, website, email, phone,
description, nif ni zone.

#### Scenario: Formulario MVP
- **WHEN** el actor crea o edita un workplace
- **THEN** puede informar name, address, cp, town (vía cascada),
  Principal (`featured`) y status; no ve campos v1 fuera del MVP

### Requirement: Principal (`featured`) único por company
El sistema SHALL permitir como máximo un workplace `featured` (UI:
**Principal**) por company. Al marcar uno como principal, SHALL
desmarcar el anterior de esa company. El primer workplace creado de una
company SHALL quedar featured por defecto. El centro principal SHALL
representar la dirección operativa por defecto (facturación, pedidos y
usos futuros); el consumo en documentos queda fuera de este change.

#### Scenario: Marcar principal
- **WHEN** el actor marca un workplace como Principal y ya existía otro
- **THEN** solo el marcado queda featured=true

#### Scenario: Primer centro
- **WHEN** se crea el primer workplace de la company
- **THEN** queda featured=true

#### Scenario: Default switch Principal en Create
- **WHEN** el actor abre Create y no hay workplaces Active
- **THEN** el Switch Principal inicia en true
- **WHEN** el actor abre Create y ya existe ≥1 workplace Active
- **THEN** el Switch Principal inicia en false

### Requirement: Layout Create/Edit Workplace
El formulario Create/Edit SHALL, en viewports grandes, colocar Dirección
(~75%) y Código postal (~25%) en la misma fila, y Estado (~25%) junto al
Switch Principal. SHALL NOT usar el label «Destacado»; la UI usa
«Principal».

#### Scenario: Fila dirección y estado
- **WHEN** el actor abre Create o Edit de un centro de trabajo en
  viewport grande
- **THEN** ve Dirección+CP en una fila y Estado+Principal en otra

#### Scenario: Acciones form y Volver
- **WHEN** el actor está en Create o Edit
- **THEN** Guardar/Cancelar están alineados a la derecha (`FormActions`)
  y Volver lleva a `/admin/workplaces`

### Requirement: Menú y Functionality workplaces
El sistema SHALL registrar Functionality `workplaces` bajo Module
`empresas` y exponer el ítem de menú Centros de trabajo hacia
`/admin/workplaces`, con `.view` usable junto a `empresas.access` en
roles básicos.

#### Scenario: Entrada de menú
- **WHEN** un usuario con `empresas.access` y `workplaces.view` obtiene
  el menú
- **THEN** ve Centros de trabajo bajo Empresas y puede abrir el Index
