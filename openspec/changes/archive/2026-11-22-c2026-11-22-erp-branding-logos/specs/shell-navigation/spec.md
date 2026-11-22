# Spec Delta

## ADDED Requirements

### Requirement: Variantes de logo ERP según estado del aside
El sistema SHALL mostrar el wordmark ampliado del ERP (`branding.logo_url`)
en la franja superior del aside cuando el aside está expandido en desktop/tablet
o abierto en móvil. SHALL mostrar el símbolo del ERP (`branding.mark_url`)
cuando el aside está en modo rail. En todos esos estados la franja del logo ERP
SHALL permanecer por encima de la franja del logo (o nombre) de la company.

#### Scenario: Aside expandido usa wordmark
- **WHEN** el usuario ve el AppLayout con aside expandido (desktop/tablet)
- **THEN** la franja superior muestra el logo ampliado del ERP y debajo el
  logo o nombre de la company de sesión

#### Scenario: Aside móvil abierto usa wordmark
- **WHEN** el usuario abre el aside off-canvas en viewport móvil
- **THEN** la franja superior muestra el logo ampliado del ERP por encima
  del logo o nombre de la company

#### Scenario: Rail usa símbolo
- **WHEN** el aside está en modo rail
- **THEN** la franja superior muestra el símbolo del ERP (no el wordmark
  horizontal completo) por encima del logo o inicial de company reducido

## MODIFIED Requirements

### Requirement: Aside con logos y Dashboard fijo
El sistema SHALL mostrar en el aside del `AppLayout`, en este orden: (1) espacio para el
logo del ERP (marca de producto vía props `branding`), (2) espacio para el logo de la
company de sesión si existe (vacío si no hay logo), y (3) el menú de navegación. El logo
del ERP SHALL presidir visualmente al de la company en aside expandido y en aside móvil
abierto. SHALL incluir siempre un ítem Dashboard (enlace a la ruta dashboard existente o
placeholder mínimo) para cualquier usuario autenticado con acceso al ERP, aunque no haya
otros módulos elegibles. SHALL NOT inventar entradas a Tienda, Consultas ni maestros
Fase 1 aún no implementados.

#### Scenario: Orden del aside
- **WHEN** un usuario entra al ERP con AppLayout
- **THEN** ve zona logo ERP, zona logo empresa (o vacío si no hay logo), y debajo el menú

#### Scenario: ERP por encima de company en móvil abierto
- **WHEN** el usuario abre el aside en viewport móvil
- **THEN** el logo del ERP aparece por encima del logo o nombre de la company

#### Scenario: Dashboard siempre
- **WHEN** el menú se construye para un usuario con sesión de empresa (o acceso ERP)
- **THEN** existe la entrada Dashboard aunque no haya otros módulos elegibles

### Requirement: Mini-aside colapsado en desktop/tablet con persistencia
El sistema SHALL, al colapsar el aside en viewport desktop o tablet (`md` y superior),
mostrar un rail estrecho con un icono por punto principal de menú (usando el campo
`icon` del ítem, con fallback), sin ocultar el aside por completo. SHALL persistir la
preferencia colapsado/expandido entre recargas y navegaciones Inertia (p. ej.
localStorage). En viewport móvil SHALL usar aside off-canvas sin mini-rail. En modo
rail, al activar un punto principal con varios subítems SHALL mostrar un flyout junto al
icono con esos hijos; un único hijo MAY ser enlace directo. SHALL mantener el símbolo
del ERP y el logo/inicial de company en tamaño reducido en el rail.

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
- **THEN** se muestra el símbolo del ERP y, si aplica, el logo o inicial de company
  en tamaño reducido
