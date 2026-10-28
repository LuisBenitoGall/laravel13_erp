<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-28-shell-topbar
Rule: 10-architecture
Summary: Definir e implantar el bloque 1 del chrome ERP (topbar global): estructura
    alineada al ejemplo v1 (edición de pedidos) y estética coherente con el shell v2 ya
    montado (aside). Stub completo de notificaciones/notas/chat/favoritos; empresa 1 vs N;
    avatar con Perfil (página mínima) + Logout. Sin título de vista en este bloque.
Goals:
- Topbar (bloque 1) de izquierda a derecha: (1) botón comprimir/expandir aside — en
  resoluciones móvil el aside viene ya comprimido; (2) selector de idiomas; (3)
  notificaciones — icono campana con supraindicador circular del número de nuevas, al
  clicar popover/modal como en la imagen 2 de referencia (cabecera, empty state, refresh,
  «Ver todo», «Marcar todas como leídas»); (4) notas de usuario — icono post-it con
  supraindicador y modal esqueleto (funcionalidad de dominio en un change posterior);
  (5) acceso chat del ERP (stub); (6) vistas favoritas — icono tres puntos verticales
  (stub); (7) nombre de la empresa en sesión si el usuario solo está vinculado a una, o
  selector de empresas si hay más de una (empresa en sesión preseleccionada); (8) avatar
  del usuario con desplegable Perfil + Logout.
- Stub completo por ahora: UI estable sin backend de notificaciones, chat, favoritos ni
  notas. Clicks de stubs = empty UI (sin toast «próximamente»).
- Perfil: página mínima placeholder (nombre/email en lectura + mensaje de próximamente) +
  Logout real.
- Quitar el título de vista del header actual (bloque 1); el título irá en el bloque 2
  (change posterior).
- Estructura obligatoria para todas las vistas ERP una vez definida; excepciones (p. ej.
  Tienda) solo si son expresas y manifiestas.
- Estética coherente con lo ya hecho en v2 (shadcn/slate); no clonar el look Livewire de
  v1. Estructura sí según el ejemplo de referencia.
NonGoals:
- Bloque 2 (título/subtítulo de vista, ActionBar de acciones propias de cada pantalla) y
  bloque de resumen de documento — changes posteriores.
- Dominio real de notificaciones (Reverb/Echo), chat, favoritos o notas de usuario.
- Excepción Tienda u otras: no implementarlas aquí salvo declaración expresa en este
  change (no debatida como trabajo ahora).
- Rediseño completo del aside (ya cerrado en shell-navigation).
Skills:
- frontend-design
- laravel-specialist
- tenancy-rbac
Notes:
- Prerrequisito: `c2026-10-26-shell-navigation` archivado; aside (logos + menú + Dashboard
  + Configuración SA) ya montado.
- Estado actual `AppLayout`: header con `title`, `LanguageSwitcher`, select de empresa
  solo si `available_companies.length > 1`, nombre de usuario + botón logout. Switch ya
  via `POST /companies/switch`. Shared props: `tenant`, `available_companies`, `auth`.
- Catálogo `frontend.md`: piezas previstas `NotificationsPanel`, `CompanySwitcher`.
- Referencia visual: capturas v1 topbar + dropdown Notificaciones (imágenes del explore).
- Notas: Luis pedirá la funcionalidad después; este change solo hueco + modal esqueleto.
- i18n y stack: invariantes del proyecto (Inertia/React, sin Livewire); 10 no inventa
  maestros ni productos no debatidos.


## ADDED Requirements

### Requirement: Topbar bloque 1 (chrome global)
El sistema SHALL mostrar en todas las vistas que usen el layout ERP un topbar (bloque 1)
con, de izquierda a derecha: botón de colapso del aside (aside colapsado por defecto en
viewport móvil); selector de idiomas; indicador de notificaciones (campana + badge +
popover stub según referencia); indicador de notas de usuario (post-it + badge + modal
esqueleto); acceso stub al chat; acceso stub a vistas favoritas (kebab vertical); nombre
de empresa si el usuario tiene exactamente una company vinculada, o selector con la
empresa de sesión preseleccionada si tiene más de una; avatar con menú Perfil y Logout.
SHALL NOT incluir el título de la vista en este bloque. SHALL mantener coherencia visual
con el shell v2 existente y la estructura del ejemplo v1.

#### Scenario: Orden del topbar
- **WHEN** un usuario autenticado con company de sesión ve el AppLayout
- **THEN** el bloque 1 muestra en ese orden collapse, idioma, notificaciones, notas, chat,
  favoritos, empresa (texto o selector) y avatar

#### Scenario: Una vs varias empresas
- **WHEN** el usuario tiene exactamente una company vinculada
- **THEN** se muestra el nombre de esa empresa (sin selector)
- **WHEN** el usuario tiene más de una company vinculada
- **THEN** se muestra un selector con la empresa de sesión como opción elegida

#### Scenario: Móvil con aside colapsado
- **WHEN** el viewport es móvil
- **THEN** el aside parte colapsado y el botón de collapse permite expandirlo/comprimirlo

#### Scenario: Sin título en bloque 1
- **WHEN** se renderiza el topbar
- **THEN** no aparece el título de la vista en el bloque 1

---

### Requirement: Stubs de notificaciones, notas, chat y favoritos
El sistema SHALL ofrecer UI stub para notificaciones (popover con empty state y acciones
visibles sin efecto de dominio), notas (modal esqueleto), chat y favoritos. Los clicks de
esas acciones stub SHALL resultar en empty UI (sin toast de próximamente y sin backend).
Los badges MAY mostrar cero mientras no haya dominio.

#### Scenario: Popover notificaciones stub
- **WHEN** el usuario abre la campana
- **THEN** ve el panel tipo referencia (título, «No hay nuevas notificaciones» o empty
  equivalente, refresh / ver todo / marcar leídas como empty UI)

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
hacer logout. Logout SHALL cerrar la sesión como hoy.

#### Scenario: Perfil placeholder
- **WHEN** el usuario elige Perfil en el menú del avatar
- **THEN** accede a una página mínima con nombre y email en lectura y un mensaje de
  próximamente

#### Scenario: Logout
- **WHEN** el usuario elige Logout
- **THEN** se cierra la sesión y deja de acceder a rutas protegidas


## MODIFIED Requirements

### Requirement: (AppLayout / shell chrome — título fuera del topbar)
El layout ERP SHALL dejar de renderizar el título de vista dentro del header/topbar
(bloque 1). El título y la barra de acciones por vista quedan fuera de este change
(bloque 2 posterior). El topbar pasa a ser el chrome global descrito arriba (idioma,
empresa, avatar, stubs), no la cabecera de contexto de pantalla.
