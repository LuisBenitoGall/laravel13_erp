## Purpose

Index ServerTable «Usuarios empresa»: users vinculados en `user_companies` a la
company de sesión, con acciones estándar (status, detach, Edit stub), CTA
Crear hacia Create real, y acciones masivas de página (eliminar/activar/
desactivar).

## Requirements

### Requirement: Index Usuarios empresa
El sistema SHALL listar en ServerTable los users presentes en `user_companies`
para la company de sesión. SHALL ofrecer acciones de fila activar/desactivar
(status del user), eliminar (quitar vínculo a la company de sesión) y editar
navegando al Edit stub del usuario (cabecera mínima; formulario completo
diferido). SHALL mostrar control Crear usuario que navega al Create real
(`company-users.create`). El acceso al Index/table SHALL exigir
`company-users.view` (no `company-users.viewAny`). Además SHALL exponer el
desplegable de acciones masivas del contrato ServerTable (eliminar, activar,
desactivar) a la izquierda de «Mostrar/ocultar columnas», deshabilitado hasta
seleccionar filas de la página actual. Además SHALL exponer en la columna
nombre un control de ver detalle que abre la vista rápida de usuario
(`user-list-quick-view`).

#### Scenario: Filas = user_companies de sesión
- **WHEN** el usuario abre Usuarios empresa con company de sesión S
- **THEN** la tabla muestra solo users vinculados a S en `user_companies`

#### Scenario: Aislamiento entre companies
- **WHEN** el usuario tiene sesión en company A
- **THEN** no ve en Usuarios empresa los users vinculados solo a company B

#### Scenario: Crear navega a Create real
- **WHEN** el usuario autorizado pulsa Crear usuario
- **THEN** navega al formulario Create real (ya no es máscara de alta)

#### Scenario: Editar navega a Edit stub
- **WHEN** el usuario usa la acción Editar en una fila
- **THEN** navega al Edit stub del usuario (cabecera; sin formulario completo)

#### Scenario: Activar o desactivar
- **WHEN** el usuario autorizado conmuta el status de un user de la tabla
- **THEN** el `status` del user queda persistido

#### Scenario: Eliminar quita vínculo de sesión
- **WHEN** el usuario autorizado confirma eliminar en Usuarios empresa
- **THEN** se elimina el vínculo `user_companies` con la company de sesión y el
  User global no se borra por esa acción

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `company-users.view` y sin Super Admin fuerza el
  Index o el endpoint `table` de Usuarios empresa
- **THEN** recibe 403

#### Scenario: Toolbar con acciones masivas
- **WHEN** el usuario autorizado abre Usuarios empresa
- **THEN** ve el desplegable de acciones masivas a la izquierda de
  «Mostrar/ocultar columnas» (deshabilitado hasta seleccionar filas)

#### Scenario: Icono en nombre
- **WHEN** el actor abre Usuarios empresa
- **THEN** cada fila muestra el control de ver detalle a la izquierda del
  nombre

### Requirement: Acciones masivas Usuarios empresa
El Index Usuarios empresa SHALL declarar en ServerTable acciones masivas sobre
la página actual: eliminar (detach `user_companies` de sesión + confirm),
activar y desactivar status **global** del User. Cada bulk SHALL ir por Action
Admin + Policy equivalente a la fila. SHALL NOT borrar el User global al
eliminar masivo. Desactivar masivo SHALL impedir el login de esos users.

#### Scenario: Eliminar masivo con confirm
- **WHEN** el actor autorizado selecciona uno o más users de la página y
  confirma la acción masiva eliminar
- **THEN** se eliminan los vínculos `user_companies` con la company de sesión
  para esos users y los User globales no se borran por esa acción

#### Scenario: Activar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y elige
  activar
- **THEN** el status global de esos users queda activo

#### Scenario: Desactivar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y elige
  desactivar
- **THEN** el status global de esos users queda inactivo y no pueden iniciar
  sesión

### Requirement: Desactivar user es global y bloquea acceso
Desactivar un user desde Usuarios empresa (acción de fila o masiva) SHALL
persistir `users.status` inactivo a nivel global (no status por membership).
Ese user SHALL quedar imposibilitado de login al ERP. La UI SHALL indicar
de forma clara (copy/confirm) que la desactivación es global.

#### Scenario: Desactivar desde company-users
- **WHEN** un actor autorizado desactiva un user en Usuarios empresa
- **THEN** el status global del user queda inactivo y ese user no puede
  iniciar sesión

#### Scenario: Copy UI global
- **WHEN** el actor inicia desactivar (fila o masivo) en Usuarios empresa
- **THEN** ve copy i18n que aclara que la acción afecta al acceso global
  al ERP

### Requirement: Bulk Usuarios empresa respeta perímetro y permisos
Los endpoints bulk de Usuarios empresa SHALL rechazar o no aplicar ids no
vinculados a la company de sesión y SHALL responder 403 sin el permiso
update/delete correspondiente.

#### Scenario: Ids ajenos al tenant de sesión
- **WHEN** se fuerza un bulk con ids no vinculados a la company de sesión
- **THEN** esos ids no producen detach/status sobre otra company

#### Scenario: Sin permiso
- **WHEN** un actor sin el permiso de update/delete correspondiente fuerza el
  endpoint bulk
- **THEN** recibe 403
