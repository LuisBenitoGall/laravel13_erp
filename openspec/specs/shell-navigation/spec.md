## Purpose

Shell de navegación Fase 0: aside con logos ERP/company (mini-rail md+ con persistencia / off-canvas móvil), Dashboard fijo, bloque Configuración para Super Admin, menú tenant según capas.

## Requirements

### Requirement: Aside con logos y Dashboard fijo
El sistema SHALL mostrar en el aside del `AppLayout`, en este orden: (1) espacio para el
logo del ERP, (2) espacio para el logo de la company de sesión si existe (vacío si no hay
logo), y (3) el menú de navegación. SHALL incluir siempre un ítem Dashboard (enlace a la
ruta dashboard existente o placeholder mínimo) para cualquier usuario autenticado con
acceso al ERP, aunque no haya otros módulos elegibles. SHALL NOT inventar entradas a
Tienda, Consultas ni maestros Fase 1 aún no implementados.

#### Scenario: Orden del aside
- **WHEN** un usuario entra al ERP con AppLayout
- **THEN** ve zona logo ERP, zona logo empresa (o vacío si no hay logo), y debajo el menú

#### Scenario: Dashboard siempre
- **WHEN** el menú se construye para un usuario con sesión de empresa (o acceso ERP)
- **THEN** existe la entrada Dashboard aunque no haya otros módulos elegibles

### Requirement: Configuración siempre para Super Admin
El sistema SHALL, si el usuario tiene rol Super Admin, incluir en el menú un bloque
Configuración (label i18n acordado) con las funcionalidades de operador / AdminOnly del
catálogo activo (p. ej. modules, functionalities, companies, company-modules,
company-module-authorizations, roles de plataforma), sin exigir que el módulo `admin`
esté autorizado y encendido para la company de sesión. SHALL unificar bajo ese bloque el
agrupado previo «Plataforma». SHALL NOT mostrar el bloque Configuración de operador
AdminOnly a usuarios que no sean Super Admin. SHALL NOT ampliar el perímetro de datos:
solo companies vinculadas.

#### Scenario: SA ve Configuración sin módulo admin encendido
- **WHEN** un Super Admin tiene company de sesión cuyo módulo admin no está encendido
- **THEN** el menú incluye Configuración con los submenús operador existentes

#### Scenario: No SA sin Configuración operador
- **WHEN** un usuario sin rol Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración de operador AdminOnly

### Requirement: Menú tenant según capas (salvo excepciones de shell)
El sistema SHALL seguir construyendo el resto del menú (no Dashboard, no Configuración
SA) según módulos Active + autorizados + encendidos y permisos del usuario
(Basic/Optional), alineado a R-AUT-08. SHALL agrupar o etiquetar de forma coherente con
cuenta / empresa donde haya funcionalidades existentes (p. ej. company-roles bajo
empresa), sin inventar enlaces a maestros aún no implementados.

#### Scenario: Tenant ve ítems de módulos elegibles
- **WHEN** un manager tiene un módulo Basic/Optional autorizado y encendido y permiso
  `viewAny` (o comodín)
- **THEN** esos ítems aparecen en el menú además de Dashboard

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
