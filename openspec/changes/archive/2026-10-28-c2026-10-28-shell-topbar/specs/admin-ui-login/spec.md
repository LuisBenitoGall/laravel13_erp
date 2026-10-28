## MODIFIED Requirements

### Requirement: Catálogo UI mínimo y login
El sistema SHALL ofrecer un `AppLayout` con sidebar data-driven (`Module`/`Functionality`,
coloreado por módulo) y **topbar bloque 1** (chrome global: collapse, idioma,
notificaciones/notas/chat/favoritos stub, empresa, avatar), páginas Index de Module y
Functionality con `ServerTable`, kit de formularios mínimo Create/Edit, y un login mínimo
con el guard de sesión estándar de Laravel. El `AppLayout` SHALL NOT renderizar el título
de la vista dentro del topbar (bloque 1). Logout SHALL estar disponible desde el menú del
avatar (además de seguir cerrando sesión correctamente).

#### Scenario: Login del superadmin
- **WHEN** el superadmin sembrado introduce sus credenciales en el login
- **THEN** accede a la aplicación y ve el `AppLayout` con el menú construido a partir de
  `Module`/`Functionality` y el topbar bloque 1

#### Scenario: Listado de Module con ServerTable
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un `ServerTable` (Tabulator) paginado server-side, conforme al contrato de
  `frontend.md` §3

#### Scenario: Logout
- **WHEN** el usuario autenticado cierra sesión desde el menú del avatar (o flujo
  equivalente de logout)
- **THEN** la sesión termina y las rutas protegidas redirigen al login

#### Scenario: Topbar sin título de vista
- **WHEN** una página pasa o no un `title` al AppLayout
- **THEN** el topbar (bloque 1) no muestra ese título como cabecera de chrome
