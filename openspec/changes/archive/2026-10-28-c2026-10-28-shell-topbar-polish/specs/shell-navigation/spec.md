## ADDED Requirements

### Requirement: Mini-aside colapsado en desktop/tablet con persistencia
El sistema SHALL, al colapsar el aside en viewport desktop o tablet (`md` y superior),
mostrar un rail estrecho con un icono por punto principal de menú (usando el campo
`icon` del ítem, con fallback), sin ocultar el aside por completo. SHALL persistir la
preferencia colapsado/expandido entre recargas y navegaciones Inertia (p. ej.
localStorage). En viewport móvil SHALL usar aside off-canvas sin mini-rail. En modo
rail, al activar un punto principal con varios subítems SHALL mostrar un flyout junto al
icono con esos hijos; un único hijo MAY ser enlace directo. SHALL mantener logo ERP y/o
company en tamaño reducido en el rail.

#### Scenario: Colapso desktop muestra rail
- **WHEN** el usuario colapsa el aside en desktop o tablet
- **THEN** permanece un rail con iconos de los puntos principales del menú

#### Scenario: Persistencia del colapso
- **WHEN** el usuario colapsa o expande el aside y recarga o navega a otra página ERP
- **THEN** el aside conserva el estado colapsado o expandido elegido (en el mismo tipo
  de viewport desktop/tablet)

#### Scenario: Móvil off-canvas
- **WHEN** el viewport es móvil y el aside está colapsado
- **THEN** no se muestra mini-rail; el aside usa off-canvas

#### Scenario: Flyout de subítems en rail
- **WHEN** el aside está en rail y el usuario activa un grupo con varios subítems
- **THEN** aparece un flyout junto al icono con esos hijos

#### Scenario: Logo reducido en rail
- **WHEN** el aside está en rail
- **THEN** se muestra favicon o logo reducido de ERP y/o empresa

## MODIFIED Requirements

### Requirement: Aside colapsable desde el topbar
El sistema SHALL permitir comprimir y expandir el aside del `AppLayout` mediante el botón
de collapse del topbar (bloque 1). En viewport móvil el aside SHALL iniciar colapsado
(off-canvas) salvo que el diseño de persistencia/viewport diga lo contrario al entrar
desde un estado ya preferido en desktop. En desktop/tablet, colapsar SHALL activar el
mini-aside (rail), no ocultar el aside. Las reglas de contenido del aside (logos,
Dashboard, menú tenant, Configuración SA) permanecen; este requisito define el
comportamiento de colapso/expansión.

#### Scenario: Toggle aside desde topbar
- **WHEN** el usuario pulsa el botón de collapse del topbar
- **THEN** el aside pasa entre expandido y colapsado (rail en md+, off-canvas en móvil)

#### Scenario: Default móvil colapsado
- **WHEN** el usuario carga el AppLayout en viewport móvil
- **THEN** el aside está colapsado (off-canvas) hasta que lo expanda
