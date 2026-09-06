## Purpose

`AppLayout`, login/logout, `ServerTable` y formularios mínimos del catálogo Admin.

## Requirements

### Requirement: Catálogo UI mínimo y login
El sistema SHALL ofrecer un `AppLayout` con sidebar data-driven (`Module`/`Functionality`,
coloreado por módulo), páginas Index de Module y Functionality con `ServerTable`, kit de
formularios mínimo Create/Edit, y un login mínimo con el guard de sesión estándar de
Laravel.

#### Scenario: Login del superadmin
- **WHEN** el superadmin sembrado introduce sus credenciales en el login
- **THEN** accede a la aplicación y ve el `AppLayout` con el menú construido a partir de
  `Module`/`Functionality`

#### Scenario: Listado de Module con ServerTable
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un `ServerTable` (Tabulator) paginado server-side, conforme al contrato de
  `frontend.md` §3

#### Scenario: Logout
- **WHEN** el usuario autenticado cierra sesión
- **THEN** la sesión termina y las rutas protegidas redirigen al login
