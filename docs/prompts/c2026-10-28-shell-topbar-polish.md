<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-28-shell-topbar-polish
Rule: 10-architecture
Summary: Corregir el primer intento del shell topbar/aside: mini-aside con iconos (no
    ocultar en desktop/tablet), persistencia del colapso, alineación del topbar (collapse
    izq. / resto der.), y paneles stub unificados como popover bajo el icono.
Goals:
- Collapse en desktop/tablet: versión simplificada del aside (rail de iconos), un icono
  representativo por punto principal de menú; no cerrar/ocultar por completo el aside.
- Persistencia del estado colapsado/expandido al recargar o cambiar de página
  (recordar preferencia del usuario).
- Móvil: aside off-canvas (sin mini-rail); mini-aside solo desktop y tablet.
- En modo colapsado (desktop/tablet), clic en un grupo con varios subítems: flyout/popover
  con los hijos al lado del icono (opción B1).
- Logos ERP/empresa en modo colapsado: favicon o logo reducido (no desaparecer del todo).
- Topbar: a excepción del botón Collapse (izquierda), el resto de controles alineados a
  la derecha (idioma, notificaciones, notas, chat, favoritos, empresa, avatar), como en
  la referencia visual aportada.
- Unificar layout de paneles stub (notificaciones, notas, y el mismo criterio para
  cualquier panel bajo icono): siempre bajo el icono (popover), no uno centrado en
  pantalla y otro anclado. Coherencia visual al maquetar.
NonGoals:
- Dominio real de notificaciones, notas, chat o favoritos (siguen stub / empty UI).
- Bloque 2 (título de vista + ActionBar).
- Rediseño completo del menú expandido o cambio de reglas de visibilidad R-AUT-08 /
  Configuración SA.
- Cambiar el look general v2 más allá de estas correcciones de chrome.
Skills:
- frontend-design
- laravel-specialist
Notes:
- Parte del feedback post-implementación de `c2026-10-28-shell-topbar` (change ya
  aplicado/archivado). Fallo actual: aside con `hidden` al colapsar en md+; notas con
  Dialog centrado vs NotificationsPanel con Popover; topbar sin empujar el grupo a la
  derecha.
- Referencias: foto 1 (topbar collapse izq. / acciones der.); foto 2 (aside colapsado tipo
  rail de iconos v1).
- El menú data-driven ya expone `icon` por ítem principal (`AsideNavItem`); aprovecharlo
  para el rail.
- Persistencia: recordar al recargar/navegar (p. ej. localStorage — 10 delimita detalle
  técnico en design).


## ADDED Requirements

### Requirement: Mini-aside colapsado en desktop/tablet con persistencia
El sistema SHALL, al colapsar el aside en viewport desktop o tablet, mostrar un rail
estrecho con un icono por punto principal de menú (no ocultar el aside por completo).
SHALL persistir la preferencia colapsado/expandido entre recargas y navegaciones Inertia.
En viewport móvil SHALL seguir usando aside off-canvas (sin mini-rail). En modo
colapsado desktop/tablet, al activar un punto principal con varios subítems SHALL mostrar
un flyout con esos hijos junto al icono. SHALL mantener logo ERP y/o company en tamaño
reducido (favicon/logo pequeño) en el rail colapsado.

#### Scenario: Colapso desktop muestra rail
- **WHEN** el usuario colapsa el aside en desktop o tablet
- **THEN** permanece un rail con iconos de los puntos principales del menú

#### Scenario: Persistencia del colapso
- **WHEN** el usuario colapsa (o expande) el aside y recarga o navega a otra página ERP
- **THEN** el aside conserva el estado colapsado o expandido elegido

#### Scenario: Móvil off-canvas
- **WHEN** el viewport es móvil
- **THEN** el aside colapsado no usa mini-rail; usa off-canvas

#### Scenario: Flyout de subítems en rail
- **WHEN** el aside está colapsado en desktop/tablet y el usuario activa un grupo con
  varios subítems
- **THEN** aparece un flyout junto al icono con esos hijos

#### Scenario: Logo reducido en rail
- **WHEN** el aside está colapsado en desktop/tablet
- **THEN** se muestra favicon o logo reducido de ERP y/o empresa (no desaparece del todo)

---

### Requirement: Topbar alineado collapse izq. / resto der.
El sistema SHALL colocar el botón de collapse del aside a la izquierda del topbar y
alinear a la derecha el resto de controles del bloque 1 (idioma, notificaciones, notas,
chat, favoritos, empresa, avatar).

#### Scenario: Alineación del topbar
- **WHEN** se renderiza el topbar bloque 1
- **THEN** solo el collapse queda a la izquierda y el resto del grupo de acciones queda
  a la derecha

---

### Requirement: Paneles stub unificados bajo el icono
El sistema SHALL mostrar los paneles stub del topbar (al menos notificaciones y notas)
como popover anclado bajo el icono disparador, con layout coherente entre sí. SHALL NOT
abrir unos como modal centrado en pantalla y otros bajo el icono.

#### Scenario: Notas bajo el icono
- **WHEN** el usuario abre notas desde el topbar
- **THEN** el panel aparece bajo el icono (mismo criterio visual que notificaciones), no
  como dialog centrado


## MODIFIED Requirements

### Requirement: (shell-topbar / shell-navigation — comportamiento de colapso)
Aclarar/corregir el colapso del aside: en md+ mini-aside + persistencia + flyout B1 +
logos reducidos; en móvil off-canvas; topbar con split izq/der; stubs solo popover bajo
icono. 10 escribe el delta sobre `shell-topbar` / `shell-navigation` según el archive.
