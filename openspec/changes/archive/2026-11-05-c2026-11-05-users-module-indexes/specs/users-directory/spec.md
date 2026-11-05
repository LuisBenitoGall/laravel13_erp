# Spec Delta: users-directory

## Purpose

Directorio global de users («Todos los usuarios») en ServerTable bajo el Module
Usuarios, con acciones de fila limitadas a una máscara de notificación.

## ADDED Requirements

### Requirement: Index Todos los usuarios
El sistema SHALL listar todos los users en un ServerTable accesible desde el
menú Usuarios. Acciones de fila SHALL limitarse por ahora a una máscara de
envío de notificaciones. SHALL NOT ofrecer editar, activar/desactivar ni
eliminar en este Index. SHALL NOT aplicar aún filtro de perfiles públicos.

#### Scenario: Listado global
- **WHEN** un usuario autenticado con acceso a la vista abre Todos los usuarios
- **THEN** ve el ServerTable con el conjunto global de users

#### Scenario: Solo máscara de notificación
- **WHEN** el usuario usa la acción de notificación en una fila
- **THEN** obtiene la máscara UI (sin exigir envío real)

#### Scenario: Sin acciones de mutación
- **WHEN** el usuario consulta Acciones de una fila en Todos los usuarios
- **THEN** no dispone de editar, activar/desactivar ni eliminar
