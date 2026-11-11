# Spec Delta

## Purpose

Membresía User↔Company en la ficha: listado ServerTable de vínculos
`user_companies` (no Empleo) y detach del vínculo con reglas de sesión.

## ADDED Requirements

### Requirement: Pivot con posición y departamento
El sistema SHALL persistir `position` y `department` como varchar nullable
en `user_companies`.

#### Scenario: Campos nullable
- **WHEN** existe un vínculo sin posición ni departamento
- **THEN** ambas columnas pueden ser null

### Requirement: Tab Empresas con ServerTable
El tab Empresas SHALL listar todos los vínculos del user (incluida la
company de sesión) en ServerTable con columnas nombre de empresa,
posición, departamento y fecha de alta (`created_at` según idioma). Authz:
self o `company-users.update` + perímetro. SHALL NOT ofrecer alta de
vínculos en este tab.

#### Scenario: Listado completo
- **WHEN** el user tiene N companies vinculadas
- **THEN** la tabla muestra N filas con las columnas acordadas

#### Scenario: Vínculo distinto de Empleo
- **WHEN** se describe o implementa este listado
- **THEN** se trata solo de membresía `user_companies`, no de la entidad
  Empleo futura

### Requirement: Eliminar solo el vínculo
El sistema SHALL eliminar la fila de `user_companies` sin borrar el User ni
la Company. Detach de la última company del user SHALL estar permitido.

#### Scenario: Detach
- **WHEN** el actor autorizado confirma eliminar un vínculo
- **THEN** esa membership desaparece y User/Company siguen existiendo

#### Scenario: Última company
- **WHEN** se elimina el último vínculo del user
- **THEN** la operación se acepta y el user queda sin companies

### Requirement: Sesión tras detach de la company activa
Si el actor en sesión elimina su propio vínculo con la company de sesión,
el sistema SHALL transferir la sesión a otra company a la que siga
vinculado; si no queda ninguna, SHALL hacer logout.

#### Scenario: Transferencia de sesión
- **WHEN** el actor self detacha su company de sesión y le queda al menos
  otra company
- **THEN** `current_company_id` pasa a otra company vinculada

#### Scenario: Logout sin companies
- **WHEN** el actor self detacha su última company (o la de sesión y no
  queda otra)
- **THEN** se cierra la sesión (logout)
