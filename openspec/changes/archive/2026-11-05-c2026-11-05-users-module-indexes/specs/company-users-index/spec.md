# Spec Delta: company-users-index

## Purpose

Index ServerTable «Usuarios empresa»: users vinculados en `user_companies` a la
company de sesión, con acciones estándar parciales y Crear como máscara.

## ADDED Requirements

### Requirement: Index Usuarios empresa
El sistema SHALL listar en ServerTable los users presentes en `user_companies`
para la company de sesión. SHALL ofrecer acciones de fila activar/desactivar
(status del user), eliminar (quitar vínculo a la company de sesión) y editar
como máscara (sin formulario completo). SHALL mostrar control Crear usuario
como máscara (sin alta real).

#### Scenario: Filas = user_companies de sesión
- **WHEN** el usuario abre Usuarios empresa con company de sesión S
- **THEN** la tabla muestra solo users vinculados a S en `user_companies`

#### Scenario: Aislamiento entre companies
- **WHEN** el usuario tiene sesión en company A
- **THEN** no ve en Usuarios empresa los users vinculados solo a company B

#### Scenario: Crear es máscara
- **WHEN** el usuario pulsa Crear usuario
- **THEN** no completa un alta real de usuario

#### Scenario: Editar es máscara
- **WHEN** el usuario usa la acción Editar en una fila
- **THEN** no completa un formulario de edición real (máscara / stub)

#### Scenario: Activar o desactivar
- **WHEN** el usuario autorizado conmuta el status de un user de la tabla
- **THEN** el `status` del user queda persistido

#### Scenario: Eliminar quita vínculo de sesión
- **WHEN** el usuario autorizado confirma eliminar en Usuarios empresa
- **THEN** se elimina el vínculo `user_companies` con la company de sesión y el
  User global no se borra por esa acción
