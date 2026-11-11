## Purpose

Membresía User↔Company en la ficha: listado ServerTable de vínculos
`user_companies` (no Empleo), edición de position/department, y detach del
vínculo con reglas de sesión.

## Requirements

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
vínculos en este tab. SHALL permitir editar position/department vía modal
(Editar) además del detach.

#### Scenario: Listado completo
- **WHEN** el user tiene N companies vinculadas
- **THEN** la tabla muestra N filas con las columnas acordadas

#### Scenario: Vínculo distinto de Empleo
- **WHEN** se describe o implementa este listado
- **THEN** se trata solo de membresía `user_companies`, no de la entidad
  Empleo futura

#### Scenario: Tab con listado, edit y detach
- **WHEN** se abre el tab Empresas con permiso de gestión
- **THEN** hay ServerTable, acción Editar (modal) y acción eliminar
  vínculo

### Requirement: Edición de posición y departamento del vínculo
El sistema SHALL permitir actualizar `position` y `department` (nullable)
de un vínculo `user_companies` desde el tab Empresas, vía botón Editar y
modal, en superficies profile y admin company-users. Authz: self o
`company-users.update` + perímetro. SHALL NOT cambiar la company del
vínculo ni ofrecer alta de memberships en este tab.

#### Scenario: Abrir modal desde la fila
- **WHEN** el actor autorizado pulsa Editar en una fila del ServerTable
  de Empresas
- **THEN** se abre el modal con posición y departamento del vínculo

#### Scenario: Guardar position y department
- **WHEN** el actor autorizado guarda el modal con valores (o null) de
  position/department
- **THEN** el vínculo se actualiza y la tabla refleja los nuevos valores

#### Scenario: Sin cambiar la company del vínculo
- **WHEN** se edita un vínculo
- **THEN** solo cambian position/department; la company del membership
  no se modifica

#### Scenario: Ajeno sin permiso
- **WHEN** un actor no autorizado fuerza el update
- **THEN** recibe 403 o rechazo de perímetro

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
