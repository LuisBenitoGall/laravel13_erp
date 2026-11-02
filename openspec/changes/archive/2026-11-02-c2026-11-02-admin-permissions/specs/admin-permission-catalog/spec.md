# Spec Delta

## Purpose

Catálogo Admin de permisos Spatie: listado, altas custom ancladas a Functionality y
borrado de custom, conviviendo con el auto-provision canónico (5 acciones + access)
sin gestionar aún la asignación a Roles ni Users.

## ADDED Requirements

### Requirement: Index Admin de permisos Spatie
El sistema SHALL exponer un Index Admin de permisos Spatie (ServerTable) con el nombre
del permiso, la funcionalidad asociada vía prefijo del name, y Acciones. El acceso SHALL
exigir el permiso Spatie `permissions.viewAny` (Super Admin bypass vía Gate::before).

#### Scenario: Usuario autorizado abre Index
- **WHEN** un usuario con `permissions.viewAny` (o Super Admin) abre el Index de permisos
- **THEN** ve el listado ServerTable de permisos Spatie del guard web

#### Scenario: Listado muestra vínculo a Functionality
- **WHEN** el Index carga filas de permisos cuyo name sigue `{functionalitySlug}.*`
- **THEN** cada fila muestra el name Spatie y la funcionalidad derivada de ese prefijo

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `permissions.viewAny` y sin Super Admin fuerza la URL del Index
- **THEN** recibe 403

### Requirement: Create de permiso custom vinculado a Functionality
El sistema SHALL permitir crear permisos Spatie adicionales a los auto-provisionados.
Cada permiso nuevo SHALL usar el name `{functionality.slug}.{sufijo}` con Functionality
existente, name único en el guard web, y SHALL NOT aceptar sufijos reservados
`viewAny`, `view`, `create`, `update`, `delete` ni `access`. Alta exige `permissions.create`.

#### Scenario: Alta custom válida
- **WHEN** el usuario autorizado elige una Functionality y un sufijo no reservado cuyo
  name completo no exista
- **THEN** se crea el permiso Spatie `{slug}.{sufijo}` en el guard web

#### Scenario: Sufijo reservado rechazado
- **WHEN** el usuario intenta crear un permiso con sufijo canónico o `access`
- **THEN** la validación lo rechaza sin crear el permiso

#### Scenario: Name duplicado rechazado
- **WHEN** el name `{slug}.{sufijo}` ya existe
- **THEN** la validación lo rechaza por unicidad

### Requirement: Delete solo de permisos custom
El sistema SHALL permitir eliminar permisos Spatie custom (hard delete) desde Acciones
del Index cuando el usuario tenga `permissions.delete`. SHALL NOT eliminar canónicos
`{functionalitySlug}.viewAny|view|create|update|delete` ni comodines `{moduleSlug}.access`.

#### Scenario: Delete custom
- **WHEN** el usuario autorizado confirma Eliminar en un permiso custom
- **THEN** el permiso Spatie se elimina (hard delete)

#### Scenario: Canónico protegido
- **WHEN** el usuario intenta eliminar un permiso canónico o un `*.access`
- **THEN** la operación se deniega y el permiso permanece

### Requirement: Convivencia con auto-provision de Functionality
El CRUD de catálogo SHALL NOT sustituir ni reabrir el auto-provision de
`CreateFunctionality` (cinco acciones por Functionality más `{module}.access`). Los
canónicos siguen naciendo solo por ese generador (o seeders equivalentes).

#### Scenario: Nueva Functionality sigue provisionando canónicos
- **WHEN** se crea una Functionality vía el flujo existente de CreateFunctionality
- **THEN** siguen existiendo `{slug}.viewAny|view|create|update|delete` y el comodín
  `{moduleSlug}.access` sin depender del CRUD de permisos
