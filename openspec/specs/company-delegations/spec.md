## Purpose

CRUD de Delegaciones: Companies hijas de la matriz de sesión, sin cuenta SaaS,
con switcher/select limitado a matrices.

## Requirements

### Requirement: CRUD Delegaciones como Company hija
El sistema SHALL exponer un CRUD de Delegaciones (Functionality `delegations` bajo
Empresas). Una Delegación SHALL ser Company con `parent_id` = matriz de sesión,
NIF, ≥1 Workplace **asociado** (de la matriz) en el alta, y creador en
`user_companies` (roles como alta de empresa salvo cuenta SaaS). Alta con
Action/rutas/UI distintas de Mis empresas. SHALL NOT crear `company_accounts`.
Index = solo hijas de la sesión. El alta SHALL NOT crear Workplace con
`company_id` = Delegación.

#### Scenario: Alta delegación sin company_accounts
- **WHEN** un actor autorizado crea una Delegación válida desde la sesión de
  una matriz
- **THEN** existe la Company con `parent_id` = matriz de sesión, el creador
  está en `user_companies`, hay ≥1 workplace de la matriz vinculado a ella, y
  no hay fila en `company_accounts` para esa Company

#### Scenario: Alta diferenciada
- **WHEN** el actor abre el Create de Delegaciones
- **THEN** no es el mismo flujo UI/ruta que el Create de Mis empresas

#### Scenario: Index solo hijas de sesión
- **WHEN** el actor lista Delegaciones con matriz A en sesión
- **THEN** solo ve Companies con `parent_id` = A

#### Scenario: Sesión no matriz
- **WHEN** la company de sesión tiene `parent_id` no null
- **THEN** el CRUD de Delegaciones no opera (rechazo/redirect)

### Requirement: Switcher solo matrices
El dropdown de cambio de empresa y la selección post-login SHALL mostrar solo
companies del vínculo con `parent_id` null. SHALL NOT permitir CurrentCompany
con `parent_id` no null vía switch/select.

#### Scenario: Delegación no listada
- **WHEN** el usuario tiene vinculada una matriz y una o más delegaciones hijas
- **THEN** el switcher / select solo ofrece la(s) matriz(ces), no las hijas

#### Scenario: Switch a delegación rechazado
- **WHEN** se intenta `companies.switch` (o equivalente) a una company con
  `parent_id` no null
- **THEN** la operación se rechaza y la sesión no queda en esa hija

### Requirement: Delegaciones elegibles en mapeo de sedes
Las Companies hijas (Delegaciones) de la matriz de sesión SHALL ser el conjunto
elegible para el mapeo N:M Workplace–Delegación. SHALL NOT exigir cambios al
CRUD de Delegaciones más allá de ser seleccionables en ese perímetro.

#### Scenario: Solo hijas de la matriz en el selector
- **WHEN** el actor edita el mapeo de un workplace de la matriz A
- **THEN** solo puede elegir Companies con `parent_id` = A

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

### Requirement: Edit Delegación — un solo listado “Sus centros”
El Edit de Delegación SHALL mostrar un único listado de workplaces de la matriz
asociados a esa Delegación (RO respecto al mapeo; enlace a Edit de la sede).
SHALL NOT ofrecer CRUD de centros con dueño = hija ni un segundo bloque
paralelo con otra semántica. Copy en lenguaje llano (sin jerga N:M / mapa).

#### Scenario: Solo sus centros asociados
- **WHEN** el actor abre el Edit de una Delegación con sedes asociadas
- **THEN** ve esas sedes en un único listado comprensible, sin dos categorías
  técnicas de “centros”

#### Scenario: Sin centros asociados
- **WHEN** la Delegación no tiene vínculos
- **THEN** empty state + vía clara para vincular/crear sede en el flujo de
  matriz (sin crear dueño = hija)

#### Scenario: Ya no hay panel CRUD de centros de la hija
- **WHEN** un actor abre el Edit de Delegación
- **THEN** no puede crear/editar/borrar workplaces con `company_id` = esa
  Delegación desde esa ficha

### Requirement: Migración de workplaces con dueño = Delegación
Los workplaces existentes con `company_id` = Delegación SHALL pasar a
`company_id` = matriz padre y vincularse a esa Delegación en el pivote.

#### Scenario: Tras migrar
- **WHEN** se aplica la migración de datos
- **THEN** no quedan workplaces no soft-deleted con `company_id` de una
  Delegación y las asociaciones N:N reflejan la relación anterior
