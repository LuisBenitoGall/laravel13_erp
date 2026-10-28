## ADDED Requirements

### Requirement: Excepciones de shell (Dashboard y Configuración SA)
El sistema SHALL anteponer al menú data-driven un ítem Dashboard siempre presente para
usuarios con acceso al ERP. SHALL, para Super Admin, añadir el bloque Configuración con
Functionalities Active de Modules Active de level AdminOnly **sin** exigir autorización
ni activación del módulo en la company de sesión. SHALL NOT aplicar esa excepción de
menú a usuarios no Super Admin ni a módulos Basic/Optional.

#### Scenario: Dashboard ante módulos elegibles
- **WHEN** se construye el menú con o sin módulos elegibles
- **THEN** el ítem Dashboard está presente

#### Scenario: Configuración SA ignora capas de company
- **WHEN** Super Admin construye el menú y `admin` no está autorizado o encendido para
  la company de sesión
- **THEN** el bloque Configuración aparece con las Functionalities AdminOnly activas

---

### Requirement: Bypass EnsureModuleIsActive para Super Admin en AdminOnly
El sistema SHALL, al evaluar `EnsureModuleIsActive` / `module:{slug}`, conceder acceso
si el usuario es Super Admin **y** el Module del slug es `ModuleLevel::AdminOnly` **y**
el Module está Active globalmente, aunque la company de sesión no lo tenga autorizado o
encendido. SHALL seguir exigiendo Active + autorizado + encendido para Basic/Optional
también cuando el actor sea Super Admin. SHALL NOT ampliar el perímetro de datos de
companies.

#### Scenario: SA no recibe 403 module:admin
- **WHEN** un Super Admin con company de sesión navega a una ruta `/admin/*` con
  middleware `module:admin` y admin no está activo para esa company
- **THEN** la petición no aborta 403 por módulo inactivo (sí aplican auth y company)

#### Scenario: SA sigue sujeto a capas en Basic/Optional
- **WHEN** un Super Admin solicita una ruta protegida con `module:` de un módulo
  Basic u Optional no autorizado o no encendido para la company de sesión
- **THEN** el acceso se deniega por módulo inactivo

## MODIFIED Requirements

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un `Module` en el menú tenant si, para la empresa de la sesión,
el Module está Active globalmente, la empresa está autorizada a él, lo tiene encendido, y
además el usuario tiene el permiso comodín `<module-slug>.access` **o** al menos un
permiso de alguna `Functionality` del módulo. SHALL mostrar cada `Functionality` si el
usuario tiene el comodín de su módulo o su propio permiso `viewAny` — nunca exigiendo
ambos a la vez (R-AUT-08). `EnsureModuleIsActive` SHALL bloquear rutas cuyo módulo no
cumpla Active + autorizado + encendido para la empresa de sesión, **excepto** Super Admin
sobre Modules AdminOnly Active (véase bypass AdminOnly). Las excepciones de shell
(Dashboard fijo; Configuración AdminOnly siempre para Super Admin) SHALL coexistir con
esta regla sin sustituirla para Basic/Optional ni para usuarios no SA.

#### Scenario: Módulo visible por permiso de Functionality sin comodín
- **WHEN** un usuario tiene el permiso `viewAny` de una `Functionality`, el Module está
  Active, autorizado y encendido para la empresa de la sesión, pero no tiene el comodín
  `<module-slug>.access`
- **THEN** el módulo aparece en el menú con esa `Functionality` visible

#### Scenario: Comodín de módulo implica todas sus Functionalities
- **WHEN** un usuario tiene el permiso comodín `<module-slug>.access` y el Module está
  Active, autorizado y encendido para la empresa de la sesión
- **THEN** ve todas las Functionalities activas de ese módulo en el submenú, tenga o no
  permisos individuales sobre cada una

#### Scenario: Módulo visible solo si elegible y encendido
- **WHEN** se construye el menú tenant para la empresa de la sesión
- **THEN** un Module Basic/Optional solo aparece si está Active globalmente, la empresa
  está autorizada, lo tiene encendido, y el usuario cumple la regla de permisos (comodín
  OR permiso de Functionality)

#### Scenario: Menú respeta módulos encendidos
- **WHEN** un usuario no Super Admin (o un módulo no AdminOnly) tiene permisos sobre una
  Functionality cuyo Module no está encendido para la empresa de la sesión
- **THEN** esa Functionality no aparece en el menú tenant y el acceso por ruta queda
  bloqueado salvo el bypass AdminOnly para Super Admin
