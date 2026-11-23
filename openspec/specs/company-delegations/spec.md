## Purpose

CRUD de Delegaciones: Companies hijas de la matriz de sesión, sin cuenta SaaS,
con switcher/select limitado a matrices.

## Requirements

### Requirement: CRUD Delegaciones como Company hija
El sistema SHALL exponer un CRUD de Delegaciones (Functionality `delegations` bajo
Empresas). Una Delegación SHALL ser Company con `parent_id` = matriz de sesión,
NIF, ≥1 Workplace en el alta, y creador en `user_companies` (roles como alta de
empresa salvo cuenta SaaS). Alta con Action/rutas/UI distintas de Mis empresas.
SHALL NOT crear `company_accounts`. Index = solo hijas de la sesión.

#### Scenario: Alta delegación sin company_accounts
- **WHEN** un actor autorizado crea una Delegación válida desde la sesión de
  una matriz
- **THEN** existe la Company con `parent_id` = matriz de sesión, el creador
  está en `user_companies`, hay ≥1 workplace de esa company, y no hay fila en
  `company_accounts` para esa Company

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
