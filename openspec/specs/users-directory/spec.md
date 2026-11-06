## Purpose

Directorio global de users («Todos los usuarios») en ServerTable bajo el Module
Usuarios, filtrado a `is_public = true`, con acciones de fila limitadas a una
máscara de notificación.

## Requirements

### Requirement: Index Todos los usuarios
El sistema SHALL listar en ServerTable, desde el menú Usuarios, solo los users
con `is_public = true`. Acciones de fila SHALL limitarse por ahora a una
máscara de envío de notificaciones. SHALL NOT ofrecer editar,
activar/desactivar ni eliminar en este Index. El acceso al Index/table SHALL
exigir `all-users.view` (no `all-users.viewAny`).

#### Scenario: Listado global
- **WHEN** un usuario autenticado con acceso a la vista abre Todos los usuarios
  / su `table`
- **THEN** ve el ServerTable solo con users `is_public = true` (no aparecen
  los no públicos)

#### Scenario: Solo máscara de notificación
- **WHEN** el usuario usa la acción de notificación en una fila
- **THEN** obtiene la máscara UI (sin exigir envío real)

#### Scenario: Sin acciones de mutación
- **WHEN** el usuario consulta Acciones de una fila en Todos los usuarios
- **THEN** no dispone de editar, activar/desactivar ni eliminar

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `all-users.view` y sin Super Admin fuerza el Index
  o el endpoint `table` de Todos los usuarios
- **THEN** recibe 403
