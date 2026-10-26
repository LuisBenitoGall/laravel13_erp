## Purpose

Asignación UI/Action de cualquier rol Spatie local de company a usuarios vinculados (un rol a la vez); rechazo de roles de otra company o de plataforma.

## Requirements

### Requirement: Asignación de roles locales de company a miembros
El sistema SHALL permitir a un actor autorizado por `CompanyRoleGate` (manager de la
company o Super Admin) asignar a un usuario ya vinculado a la company cualquiera de los
roles Spatie con `company_id` de esa company (incluidos custom). SHALL rechazar roles
de otra company o con `company_id` NULL (plataforma). SHALL dejar al usuario con un
solo rol de esa company tras la asignación (elimina otros roles locales previos de esa
company en el team). SHALL exponer en la UI de miembros el listado de roles locales y
el rol actual del miembro (incluido custom).

#### Scenario: Asignar rol custom a miembro
- **WHEN** un manager elige un rol custom local de su company para un miembro vinculado
- **THEN** ese usuario tiene ese rol en el team de la company y no conserva otro rol
  local previo de esa company

#### Scenario: Seguir pudiendo asignar manager y guest
- **WHEN** un manager asigna `manager` o `guest` a un miembro vinculado
- **THEN** la asignación tiene éxito con el mismo gate

#### Scenario: No asignar rol de otra company ni plataforma
- **WHEN** se intenta asignar un nombre de rol que no corresponde a un rol local de la
  company del miembro
- **THEN** la operación se rechaza

#### Scenario: UI de miembros muestra todos los roles locales
- **WHEN** un actor autorizado abre la pantalla de miembros de la company
- **THEN** el selector incluye todos los roles con `company_id` de esa company y refleja
  el rol actual del miembro aunque sea custom

#### Scenario: Usuario no vinculado
- **WHEN** se intenta asignar un rol a un usuario que no pertenece a la company
- **THEN** la operación se rechaza
