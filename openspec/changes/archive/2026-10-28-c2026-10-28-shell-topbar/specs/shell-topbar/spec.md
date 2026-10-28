## ADDED Requirements

### Requirement: Topbar bloque 1 (chrome global)
El sistema SHALL mostrar en todas las vistas que usen el `AppLayout` del ERP un topbar
(bloque 1) con, de izquierda a derecha: botón de colapso del aside (aside colapsado por
defecto en viewport móvil); selector de idiomas; indicador de notificaciones (campana +
badge + popover stub); indicador de notas de usuario (post-it + badge + modal esqueleto);
acceso stub al chat; acceso stub a vistas favoritas (kebab vertical); nombre de empresa
si el usuario tiene exactamente una company en `available_companies`, o selector con la
empresa de sesión preseleccionada si tiene más de una; avatar con menú Perfil y Logout.
SHALL NOT incluir el título de la vista en este bloque. SHALL mantener coherencia visual
con el shell v2 (slate/shadcn) y la estructura del ejemplo v1 de referencia.

#### Scenario: Orden del topbar
- **WHEN** un usuario autenticado con company de sesión ve el AppLayout
- **THEN** el bloque 1 muestra en ese orden collapse, idioma, notificaciones, notas, chat,
  favoritos, empresa (texto o selector) y avatar

#### Scenario: Una vs varias empresas
- **WHEN** el usuario tiene exactamente una company en `available_companies`
- **THEN** se muestra el nombre de esa empresa sin selector
- **WHEN** el usuario tiene más de una company en `available_companies`
- **THEN** se muestra un selector con la empresa de sesión como opción elegida

#### Scenario: Móvil con aside colapsado
- **WHEN** el viewport es móvil
- **THEN** el aside parte colapsado y el botón de collapse permite expandirlo o
  comprimirlo

#### Scenario: Sin título en bloque 1
- **WHEN** se renderiza el topbar
- **THEN** no aparece el título de la vista en el bloque 1

---

### Requirement: Stubs de notificaciones, notas, chat y favoritos
El sistema SHALL ofrecer UI stub para notificaciones (popover con empty state y acciones
visibles sin efecto de dominio), notas (modal esqueleto), chat y favoritos. Los clicks de
esas acciones stub SHALL resultar en empty UI (sin toast de próximamente y sin backend).
Los badges SHALL poder mostrar cero mientras no haya dominio.

#### Scenario: Popover notificaciones stub
- **WHEN** el usuario abre la campana
- **THEN** ve el panel tipo referencia (título, empty state de no hay notificaciones,
  refresh / ver todo / marcar leídas como empty UI)

#### Scenario: Modal notas stub
- **WHEN** el usuario abre el icono de notas
- **THEN** ve un modal esqueleto sin persistencia ni listado real

#### Scenario: Chat y favoritos stub
- **WHEN** el usuario activa chat o favoritos
- **THEN** la UI no ejecuta dominio (empty UI)

---

### Requirement: Avatar con Perfil mínimo y Logout
El sistema SHALL mostrar un avatar del usuario en sesión con desplegable que permita ir a
una página de perfil mínima (nombre y email en lectura más mensaje de próximamente) y
hacer logout. Logout SHALL cerrar la sesión con el endpoint existente.

#### Scenario: Perfil placeholder
- **WHEN** el usuario elige Perfil en el menú del avatar
- **THEN** accede a una página mínima con nombre y email en lectura y un mensaje de
  próximamente

#### Scenario: Logout
- **WHEN** el usuario elige Logout
- **THEN** se cierra la sesión y las rutas protegidas redirigen al login
