## Purpose

`AppLayout` (sidebar + topbar bloque 1 + breadcrumb bloque 2 + ActionBar de vista bloque 3), login/logout vía avatar, `ServerTable` y formularios del catálogo Admin.

## Requirements

### Requirement: Catálogo UI mínimo y login
El sistema SHALL ofrecer un `AppLayout` con sidebar data-driven (`Module`/`Functionality`,
coloreado por módulo), **topbar bloque 1**, **breadcrumb bloque 2** y **ActionBar de
vista bloque 3** (`ViewActionBar` cuando la página declare acciones; véase
`shell-action-bar`), Index de Module con `ServerTable`, Create/Edit de Module conforme a
`admin-module-catalog` (Create con kit; Edit con tabs, level readonly visible, Delete en
ActionBar si no shell; Volver al Index), sin CRUD genérico de Funcionalidades (páginas ni
menú), y un login mínimo con el guard de sesión estándar de Laravel. El `AppLayout`
SHALL NOT renderizar el título de la vista dentro del topbar (bloque 1). Logout SHALL
estar disponible desde el menú del avatar.
El Index de Module SHALL ser la referencia del contrato `server-table-reference`:
header filters, columnas sin Estado, Acciones = Edit (+ Delete no shell; toggle solo
`optional`), toolbar (columnas / page size / Excel+PDF), i18n Tabulator reactiva, sin
botón de búsqueda avanzada.

#### Scenario: Login del superadmin
- **WHEN** el superadmin sembrado introduce sus credenciales en el login
- **THEN** accede a la aplicación y ve el `AppLayout` con el menú, el topbar bloque 1 y
  el breadcrumb cuando la página lo declara

#### Scenario: Listado de Module con ServerTable
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un `ServerTable` conforme al contrato de referencia actualizado
  (filtros de cabecera, sin columna Estado, Acciones = Edit + Delete en no-shell y
  toggle solo en nivel `optional`, icono Lucide con title, Nivel i18n, toolbar
  columnas/page size/Excel+PDF, i18n), sin búsqueda avanzada en la ActionBar, el
  breadcrumb, y la ActionBar de vista con al menos Crear cuando corresponda

#### Scenario: Create de Module conforme al catálogo
- **WHEN** el superadmin abre Create de Module
- **THEN** ve el formulario de alta (nombre, nivel, icono modal, colorpicker, status
  toggle, LangTabs label+explanation; sin slug) y puede guardar hacia Edit

#### Scenario: Edit de Module con tabs
- **WHEN** el superadmin abre Edit de Module
- **THEN** ve pestañas Módulo y Funcionalidades con CRUD anidado (ServerTable +
  ActionBar Nueva + modales), level visible readonly, Delete en ActionBar si no es
  shell, formulario sin slug editable, y Volver lleva al Index de Módulos

#### Scenario: Logout
- **WHEN** el usuario autenticado cierra sesión desde el menú del avatar (o flujo
  equivalente de logout)
- **THEN** la sesión termina y las rutas protegidas redirigen al login

#### Scenario: Topbar sin título de vista
- **WHEN** una página declara breadcrumb y/o title para document head
- **THEN** el topbar (bloque 1) no muestra ese título como cabecera de chrome; el
  contexto de vista aparece en el breadcrumb
