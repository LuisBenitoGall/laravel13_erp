## MODIFIED Requirements

### Requirement: Topbar bloque 1 (chrome global)
El sistema SHALL mostrar en todas las vistas que usen el `AppLayout` del ERP un topbar
(bloque 1) con: botón de colapso del aside a la **izquierda**; y a la **derecha**, en
este orden: selector de idiomas; indicador de notificaciones (campana + badge + popover
stub); indicador de notas de usuario (post-it + badge + popover stub); acceso stub al
chat; acceso stub a vistas favoritas (kebab vertical); nombre de empresa si el usuario
tiene exactamente una company en `available_companies`, o selector con la empresa de
sesión preseleccionada si tiene más de una; avatar con menú Perfil y Logout. El aside
colapsado por defecto en viewport móvil sigue aplicando. SHALL NOT incluir el título de
la vista ni el breadcrumb option/suboption en este bloque (eso pertenece al capability
`shell-breadcrumb` bajo el topbar). SHALL NOT incluir ActionBar. SHALL mantener
coherencia visual con el shell v2 (slate/shadcn) y la estructura del ejemplo v1 de
referencia.

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
