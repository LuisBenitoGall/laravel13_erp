## MODIFIED Requirements

### Requirement: Index Todos los usuarios
El sistema SHALL listar todos los users en un ServerTable accesible desde el
menú Usuarios. Acciones de fila SHALL limitarse por ahora a una máscara de
envío de notificaciones. SHALL NOT ofrecer editar, activar/desactivar ni
eliminar en este Index. SHALL NOT aplicar aún filtro de perfiles públicos. El
acceso al Index/table SHALL exigir `all-users.view` (no `all-users.viewAny`).

#### Scenario: Listado global
- **WHEN** un usuario autenticado con acceso a la vista abre Todos los usuarios
- **THEN** ve el ServerTable con el conjunto global de users

#### Scenario: Solo máscara de notificación
- **WHEN** el usuario usa la acción de notificación en una fila
- **THEN** obtiene la máscara UI (sin exigir envío real)

#### Scenario: Sin acciones de mutación
- **WHEN** el usuario consulta Acciones de una fila en Todos los usuarios
- **THEN** no dispone de editar, activar/desactivar ni eliminar

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `all-users.view` y sin Super Admin fuerza el Index
  o el endpoint `table` de Todos los usuarios
- **THEN** recibe 403
