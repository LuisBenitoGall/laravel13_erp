# Spec Delta: menu-core-pillars

## ADDED Requirements

### Requirement: Menú Usuarios con Usuarios empresa y Todos
El sistema SHALL exponer bajo el Module `usuarios` las entradas de menú
«Usuarios empresa» (Functionality `company-users` → Index Usuarios empresa) y
«Todos los usuarios» (Functionality de directorio global). Este change SHALL NOT
exigir la entrada Roles de empresa bajo Usuarios ni bajo Empresas.

#### Scenario: Submenú Usuarios
- **WHEN** un usuario con acceso al Module Usuarios obtiene el menú
- **THEN** ve Usuarios empresa y Todos los usuarios

#### Scenario: Usuarios empresa abre Index company-users
- **WHEN** un usuario con company de sesión abre Usuarios empresa
- **THEN** navega al Index ServerTable de Usuarios empresa (no al Members HTML
  legacy como destino de menú)

## REMOVED Requirements

### Requirement: Usuarios empresa temporal hacia Members
**Reason**: Sustituido por Index ServerTable Usuarios empresa.
**Migration**: Menú `company-users` → nueva ruta Index; Members puede seguir
para asignación de roles desde otros flujos.
