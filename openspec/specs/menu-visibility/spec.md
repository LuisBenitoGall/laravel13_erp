## Purpose

Visibilidad de menú R-AUT-08: Module Active + autorizado + encendido para la
empresa de sesión, más regla AND comodín∩Functionality. Raíces solo desde
Modules del catálogo (Dashboard hoja, Configuración SA AdminOnly). Bypass
`EnsureModuleIsActive` AdminOnly para Super Admin (`configuracion`).

## Requirements

### Requirement: Raíces de menú solo desde Modules
El sistema SHALL construir las raíces del menú exclusivamente desde filas
`modules` (más Functionalities hijas). SHALL NOT inventar raíces hardcode
(dashboard/empresas/configuracion sintéticos). Dashboard SHALL ser Module sin
Functionalities hijas y aparecer como ítem clicable a su href.

#### Scenario: Dashboard desde catálogo
- **WHEN** existe Module `dashboard` Active y el usuario tiene `dashboard.access`
  (y capas company si aplican)
- **THEN** el menú muestra Dashboard como ítem clicable sin submenú

#### Scenario: Sin raíz fantasma
- **WHEN** se genera el menú
- **THEN** no existen entradas cuya raíz no corresponda a un Module del catálogo

### Requirement: Estructura mínima sembrada
El sistema SHALL sembrar (o migrar) Modules `dashboard`, `cuenta`, `usuarios`,
`empresas`, `configuracion` con levels Basic/AdminOnly según PDF, y reasignar
Functionalities existentes a esos Modules. Modules `admin` y `workspace` SHALL
dejar de existir como slugs de catálogo tras la migración.

#### Scenario: Configuración SA
- **WHEN** un Super Admin con permisos adecuados obtiene el menú
- **THEN** ve el Module `configuracion` con las Functionalities de catálogo
  reubicadas (módulos, roles, permisos, países, monedas, …)

#### Scenario: admin y workspace retirados
- **WHEN** se consulta el catálogo Modules tras la migración
- **THEN** no existen Modules con slug `admin` ni `workspace`

### Requirement: Permiso Module AND Functionality
Para acceder a una Functionality el usuario SHALL tener el comodín
`{module}.access` del Module padre **y** el permiso de acción de esa
Functionality. SHALL NOT bastar solo el `viewAny` de la Functionality sin el
comodín del Module. El estándar kernel SHALL reflejar esta regla AND.

#### Scenario: Solo viewAny de Functionality
- **WHEN** un usuario tiene `countries.viewAny` pero no `configuracion.access`
- **THEN** no ve Países en el menú y recibe 403 al forzar la URL

#### Scenario: Solo comodín de Module
- **WHEN** un usuario tiene `configuracion.access` pero no `countries.viewAny`
- **THEN** no ve ni usa Países (salvo Super Admin bypass)

### Requirement: Cache de catálogo de menú
El sistema SHALL cachear el árbol Modules/Functionalities Active y invalidarlo
al mutar Module o Functionality. El filtrado por company y permisos SHALL
aplicarse por request sobre ese catálogo cacheado. El valor cacheado SHALL ser
payload plano (arrays), no modelos Eloquent.

#### Scenario: Invalidación tras crear Functionality
- **WHEN** se crea o actualiza una Functionality Active
- **THEN** la siguiente construcción de menú refleja el cambio sin reiniciar la app

#### Scenario: Filtro company fuera de cache
- **WHEN** dos companies tienen distinto conjunto de módulos encendidos
- **THEN** cada request obtiene un menú filtrado a su company aunque compartan
  el mismo catálogo cacheado

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un `Module` en el menú tenant si, para la empresa de la
sesión, el Module está Active globalmente, la empresa está autorizada a él, lo
tiene encendido, y el usuario cumple la regla de permisos AND: comodín
`<module-slug>.access` y, para cada `Functionality` hija visible, el permiso
`viewAny` de esa Functionality. Un Module sin Functionalities (hoja) SHALL
aparecer si el usuario tiene el comodín y las tres capas. SHALL NOT mostrar una
Functionality con solo `viewAny` sin comodín, ni con solo comodín sin `viewAny`.
`EnsureModuleIsActive` SHALL bloquear rutas cuyo módulo no cumpla Active +
autorizado + encendido para la empresa de sesión, **excepto** Super Admin sobre
Modules AdminOnly Active (véase bypass AdminOnly). La excepción de shell
Configuración SA (Module AdminOnly sin capas company) SHALL coexistir con esta
regla sin sustituirla para Basic/Optional ni para usuarios no SA.

#### Scenario: Functionality exige comodín y viewAny
- **WHEN** un usuario tiene `countries.viewAny` y `configuracion.access`, y el
  Module está Active, autorizado y encendido (o excepción SA AdminOnly)
- **THEN** Países aparece en el menú bajo `configuracion`

#### Scenario: Sin comodín no basta viewAny
- **WHEN** un usuario tiene el permiso `viewAny` de una `Functionality` pero no
  el comodín `<module-slug>.access`
- **THEN** esa Functionality no aparece en el menú

#### Scenario: Comodín solo no implica todas las Functionalities
- **WHEN** un usuario tiene el permiso comodín `<module-slug>.access` pero no
  `viewAny` de una Functionality concreta del módulo
- **THEN** esa Functionality no aparece en el submenú (salvo Super Admin bypass)

#### Scenario: Módulo visible solo si elegible y encendido
- **WHEN** se construye el menú tenant para la empresa de la sesión
- **THEN** un Module Basic/Optional solo aparece si está Active globalmente, la
  empresa está autorizada, lo tiene encendido, y el usuario cumple la regla AND
  de permisos

#### Scenario: Menú respeta módulos encendidos
- **WHEN** un usuario no Super Admin (o un módulo no AdminOnly) tiene permisos
  sobre una Functionality cuyo Module no está encendido para la empresa de la
  sesión
- **THEN** esa Functionality no aparece en el menú tenant y el acceso por ruta
  queda bloqueado salvo el bypass AdminOnly para Super Admin

### Requirement: Excepciones de shell (Dashboard y Configuración SA)
El sistema SHALL obtener Dashboard desde el Module `dashboard` del catálogo (ítem
hoja clicable), no como entrada hardcode ajena al catálogo. SHALL, para Super
Admin, mostrar el Module `configuracion` (AdminOnly Active) con sus
Functionalities Active **sin** exigir autorización ni activación del módulo en
la company de sesión. SHALL NOT aplicar esa excepción de menú a usuarios no
Super Admin ni a módulos Basic/Optional.

#### Scenario: Dashboard ante módulos elegibles
- **WHEN** se construye el menú y el usuario tiene `dashboard.access` con Module
  `dashboard` Active y capas company satisfechas
- **THEN** el ítem Dashboard está presente como Module hoja clicable

#### Scenario: Configuración SA ignora capas de company
- **WHEN** Super Admin construye el menú y `configuracion` no está autorizado o
  encendido para la company de sesión
- **THEN** el Module `configuracion` aparece con sus Functionalities AdminOnly
  activas visibles según bypass SA

### Requirement: Bypass EnsureModuleIsActive para Super Admin en AdminOnly
El sistema SHALL, al evaluar `EnsureModuleIsActive` / `module:{slug}`, conceder
acceso si el usuario es Super Admin **y** el Module del slug es
`ModuleLevel::AdminOnly` **y** el Module está Active globalmente, aunque la
company de sesión no lo tenga autorizado o encendido. SHALL seguir exigiendo
Active + autorizado + encendido para Basic/Optional también cuando el actor sea
Super Admin. SHALL NOT ampliar el perímetro de datos de companies. El slug
AdminOnly de catálogo de plataforma SHALL ser `configuracion` (no `admin`).

#### Scenario: SA no recibe 403 module:configuracion
- **WHEN** un Super Admin con company de sesión navega a una ruta `/admin/*`
  protegida por middleware `module:configuracion` (AdminOnly, sucesor de
  `admin`) y ese Module no está activo para esa company
- **THEN** la petición no aborta 403 por módulo inactivo (sí aplican auth y
  company)

#### Scenario: SA sigue sujeto a capas en Basic/Optional
- **WHEN** un Super Admin solicita una ruta protegida con `module:` de un módulo
  Basic u Optional no autorizado o no encendido para la company de sesión
- **THEN** el acceso se deniega por módulo inactivo
