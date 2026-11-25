# Spec Delta — company-delegations

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

## MODIFIED Requirements

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
