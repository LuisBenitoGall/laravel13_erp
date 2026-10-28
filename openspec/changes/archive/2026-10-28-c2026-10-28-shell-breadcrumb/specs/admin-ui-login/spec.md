## MODIFIED Requirements

### Requirement: Catálogo UI mínimo y login
El sistema SHALL ofrecer un `AppLayout` con sidebar data-driven (`Module`/`Functionality`,
coloreado por módulo), **topbar bloque 1** (chrome global) y **breadcrumb bloque 2**
(option + suboption i18n bajo el topbar; véase `shell-breadcrumb`), páginas Index de
Module y Functionality con `ServerTable`, kit de formularios mínimo Create/Edit, y un
login mínimo con el guard de sesión estándar de Laravel. El `AppLayout` SHALL NOT
renderizar el título de la vista dentro del topbar (bloque 1). Logout SHALL estar
disponible desde el menú del avatar.

#### Scenario: Login del superadmin
- **WHEN** el superadmin sembrado introduce sus credenciales en el login
- **THEN** accede a la aplicación y ve el `AppLayout` con el menú, el topbar bloque 1 y
  el breadcrumb cuando la página lo declara

#### Scenario: Listado de Module con ServerTable
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un `ServerTable` (Tabulator) paginado server-side, conforme al contrato de
  `frontend.md` §3, y el breadcrumb de la página

#### Scenario: Logout
- **WHEN** el usuario autenticado cierra sesión desde el menú del avatar (o flujo
  equivalente de logout)
- **THEN** la sesión termina y las rutas protegidas redirigen al login

#### Scenario: Topbar sin título de vista
- **WHEN** una página declara breadcrumb y/o title para document head
- **THEN** el topbar (bloque 1) no muestra ese título como cabecera de chrome; el
  contexto de vista aparece en el breadcrumb
