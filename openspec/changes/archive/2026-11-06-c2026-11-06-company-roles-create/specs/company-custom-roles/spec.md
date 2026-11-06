## ADDED Requirements

### Requirement: Create rol empresa solo metadatos
El sistema SHALL exponer un formulario Create de rol de la company de sesión
con nombre obligatorio y descripción opcional, sin control ni mención de
universal y sin matriz de permisos. Tras un alta válida SHALL persistir el rol
con `company_id` de la sesión y `universal = false`, y redirigir al Edit de
ese rol. El acceso a Create SHALL exigir el permiso `company-roles.create`.

#### Scenario: Alta tenant sin universal ni matriz
- **WHEN** un usuario con `company-roles.create` abre Create de rol de empresa
- **THEN** ve nombre y descripción, no ve universal ni matriz de permisos

#### Scenario: Persistencia local no universal y redirect Edit
- **WHEN** guarda un rol nuevo válido
- **THEN** el rol queda con `company_id` de la company de sesión y
  `universal = false`, y es redirigido al Edit de ese rol

### Requirement: CTA Crear del Index roles empresa
El Index de roles de empresa SHALL ofrecer un control Crear que navega al
Create de rol de empresa (no stub a destino incorrecto).

#### Scenario: CTA Crear navega a Create
- **WHEN** un usuario autorizado pulsa Crear en el Index de roles empresa
- **THEN** navega al formulario Create de rol de empresa

## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El sistema SHALL permitir listar, crear y editar roles Spatie con `company_id`
de la company activa, y borrar roles custom de esa company, a usuarios
autorizados según Policy/permisos Spatie del módulo (Create: permiso
`company-roles.create`). Super Admin sigue el bypass de permisos del kernel
sin ampliar el perímetro de companies. SHALL impedir borrar los roles
locales `manager` y `guest`. SHALL impedir alterar los permisos del rol
local `manager` vía este CRUD. SHALL permitir editar permisos de `guest` y
de roles custom. SHALL rechazar el borrado de un rol custom que tenga
usuarios asignados. El escenario de creación SHALL alinearse al Create por
metadatos (sin exigir asignación de permisos en el alta); Edit/matriz/
borrado no se rediseñan en este change salvo lo necesario para que Create
compile y redirija a Edit.

#### Scenario: Crear rol custom en la company
- **WHEN** un usuario con `company-roles.create` crea un rol con nombre (y
  descripción opcional) en su company activa, sin asignar permisos en el alta
- **THEN** el rol queda con `company_id` de esa company y `universal = false`

#### Scenario: No borrar manager ni guest locales
- **WHEN** un manager intenta eliminar el rol local `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Matriz del manager local no editable
- **WHEN** un manager intenta guardar permisos distintos en el rol local `manager`
- **THEN** la operación se rechaza o los permisos no cambian

#### Scenario: Editar guest o custom
- **WHEN** un manager guarda permisos en `guest` o en un rol custom
- **THEN** `role_has_permissions` de ese rol local refleja la selección permitida

#### Scenario: No borrar custom con usuarios
- **WHEN** un manager intenta eliminar un rol custom con usuarios en `model_has_roles`
- **THEN** la operación se rechaza

#### Scenario: Aislamiento entre companies
- **WHEN** un manager de la company A intenta editar o borrar un rol con
  `company_id` de la company B
- **THEN** la operación se rechaza (404/403)
