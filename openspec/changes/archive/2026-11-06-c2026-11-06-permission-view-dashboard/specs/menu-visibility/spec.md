## MODIFIED Requirements

### Requirement: Permiso Module AND Functionality
Para acceder a una Functionality el usuario SHALL tener el comodín
`{module}.access` del Module padre **y** el permiso de dashboard
`{functionality}.view`. SHALL NOT bastar solo el `.view` (ni el `.viewAny`) de
la Functionality sin el comodín del Module. El estándar kernel SHALL reflejar
esta regla AND con `.view` como permiso de menú/dashboard.

#### Scenario: Solo viewAny de Functionality
- **WHEN** un usuario tiene `countries.view` pero no `configuracion.access`
- **THEN** no ve Países en el menú y recibe 403 al forzar la URL

#### Scenario: Solo comodín de Module
- **WHEN** un usuario tiene `configuracion.access` pero no `countries.view`
- **THEN** no ve ni usa Países (salvo Super Admin bypass)

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un `Module` en el menú tenant si, para la empresa de la
sesión, el Module está Active globalmente, la empresa está autorizada a él, lo
tiene encendido, y el usuario cumple la regla de permisos AND: comodín
`<module-slug>.access` y, para cada `Functionality` hija visible, el permiso
`view` de esa Functionality. Un Module sin Functionalities (hoja) SHALL
aparecer si el usuario tiene el comodín y las tres capas. SHALL NOT mostrar una
Functionality con solo `view` sin comodín, ni con solo comodín sin `view`.
`EnsureModuleIsActive` SHALL bloquear rutas cuyo módulo no cumpla Active +
autorizado + encendido para la empresa de sesión, **excepto** Super Admin sobre
Modules AdminOnly Active (véase bypass AdminOnly). La excepción de shell
Configuración SA (Module AdminOnly sin capas company) SHALL coexistir con esta
regla sin sustituirla para Basic/Optional ni para usuarios no SA.

#### Scenario: Functionality exige comodín y viewAny
- **WHEN** un usuario tiene `countries.view` y `configuracion.access`, y el
  Module está Active, autorizado y encendido (o excepción SA AdminOnly)
- **THEN** Países aparece en el menú bajo `configuracion`

#### Scenario: Sin comodín no basta viewAny
- **WHEN** un usuario tiene el permiso `view` de una `Functionality` pero no
  el comodín `<module-slug>.access`
- **THEN** esa Functionality no aparece en el menú

#### Scenario: Comodín solo no implica todas las Functionalities
- **WHEN** un usuario tiene el permiso comodín `<module-slug>.access` pero no
  `view` de una Functionality concreta del módulo
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
