## Purpose

Chrome topbar bloque 1 del AppLayout: collapse a la izquierda; idioma/stubs/empresa/avatar a la derecha; stubs como popover bajo icono. Título/breadcrumb de vista viven en `shell-breadcrumb` bajo el topbar; sin ActionBar.

## Requirements

### Requirement: Topbar bloque 1 (chrome global)
El sistema SHALL mostrar en todas las vistas que usen el `AppLayout` del ERP un topbar
(bloque 1) con: botón de colapso del aside a la **izquierda**; y a la **derecha**, en
este orden: selector de idiomas; indicador de notificaciones (campana + badge + popover
stub); indicador de notas de usuario (post-it + badge + popover stub); acceso stub al
chat; acceso stub a vistas favoritas (kebab vertical); nombre de empresa si el usuario
tiene exactamente una company elegible (matriz) en `available_companies`, o selector con
la empresa de sesión preseleccionada si tiene más de una; avatar con menú Perfil y
Logout. `available_companies` SHALL incluir solo companies del vínculo con
`parent_id` null (no Delegaciones). El aside colapsado por defecto en viewport móvil
sigue aplicando. SHALL NOT incluir el título de la vista ni el breadcrumb
option/suboption en este bloque (eso pertenece al capability `shell-breadcrumb` bajo el
topbar). SHALL NOT incluir ActionBar. SHALL mantener coherencia visual con el shell v2
(slate/shadcn) y la estructura del ejemplo v1 de referencia.

#### Scenario: Orden del topbar
- **WHEN** un usuario autenticado con company de sesión ve el AppLayout
- **THEN** el bloque 1 muestra collapse a la izquierda y, a la derecha, idioma,
  notificaciones, notas, chat, favoritos, empresa y avatar en ese orden

#### Scenario: Una vs varias empresas
- **WHEN** el usuario tiene exactamente una company en `available_companies`
- **THEN** se muestra el nombre de esa empresa sin selector
- **WHEN** el usuario tiene más de una company en `available_companies`
- **THEN** se muestra un selector con la empresa de sesión como opción elegida

#### Scenario: Móvil con aside colapsado
- **WHEN** el viewport es móvil
- **THEN** el aside parte colapsado (off-canvas) y el botón de collapse permite
  expandirlo o comprimirlo

#### Scenario: Sin título en bloque 1
- **WHEN** se renderiza el topbar
- **THEN** no aparece el título de la vista ni el breadcrumb option/suboption en el
  bloque 1

#### Scenario: Switcher sin delegaciones
- **WHEN** el usuario tiene matriz y delegaciones hijas vinculadas
- **THEN** el control de empresa del topbar no lista las hijas

### Requirement: Topbar alineado collapse izq. / resto der.
El sistema SHALL colocar el botón de collapse del aside a la izquierda del topbar y
alinear a la derecha el resto de controles del bloque 1 (idioma, notificaciones, notas,
chat, favoritos, empresa, avatar), en ese orden dentro del grupo derecho.

#### Scenario: Alineación del topbar
- **WHEN** se renderiza el topbar bloque 1
- **THEN** solo el collapse queda a la izquierda y el resto del grupo de acciones queda
  a la derecha

### Requirement: Stubs de notificaciones, notas, chat y favoritos
El sistema SHALL ofrecer UI stub para notificaciones como **popover
anclado bajo el icono** (cabecera + empty state; acciones visibles sin
efecto de dominio), y stub de chat. Los favoritos SHALL abrir el
formulario real de Preference. Las notas SHALL dejar de ser stub vacío:
el icono de notas SHALL abrir un **modal/dialog** real para crear una
Note (título + nota). Los paneles stub (notificaciones, chat) SHALL
compartir anclaje bajo el icono. Los clicks stub de notificaciones y
chat SHALL resultar en empty UI. Los badges SHALL poder mostrar cero
mientras no haya dominio; el badge de notas SHALL reflejar el conteo
real del user cuando haya Notes.

#### Scenario: Popover notificaciones stub
- **WHEN** el usuario abre la campana
- **THEN** ve el panel tipo referencia (título, empty state de no hay
  notificaciones, refresh / ver todo / marcar leídas como empty UI)
  anclado bajo el icono

#### Scenario: Popover notas stub
- **WHEN** el usuario abre el icono de notas
- **THEN** ve el modal de nueva nota (formulario título + nota), no un
  empty stub

#### Scenario: Chat y favoritos stub
- **WHEN** el usuario activa chat
- **THEN** la UI no ejecuta dominio (empty UI)

#### Scenario: Kebab favoritos real
- **WHEN** el usuario activa el icono de favoritos (tres puntos)
- **THEN** ve el formulario para nombrar y guardar la vista actual (no
  empty stub)

### Requirement: Avatar con Perfil mínimo y Logout
El sistema SHALL mostrar un avatar del usuario en sesión (imagen featured si
existe; si no, placeholder/iniciales) con desplegable que permita ir a la
ficha de perfil (`/admin/profile`) y hacer logout. Logout SHALL cerrar la
sesión con el endpoint existente.

#### Scenario: Avatar con featured
- **WHEN** el usuario en sesión tiene imagen featured
- **THEN** el topbar muestra esa imagen como avatar

#### Scenario: Avatar sin featured
- **WHEN** el usuario en sesión no tiene imagen featured
- **THEN** el topbar muestra el placeholder/iniciales

#### Scenario: Perfil placeholder
- **WHEN** el usuario elige Perfil en el menú del avatar
- **THEN** accede a `/admin/profile` (ficha Edit unificada)

#### Scenario: Logout
- **WHEN** el usuario elige Logout
- **THEN** se cierra la sesión y las rutas protegidas redirigen al login
