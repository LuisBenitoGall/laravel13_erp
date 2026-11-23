# Spec Delta — menu-core-pillars

## MODIFIED Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares como Modules del catálogo:
`empresas` (con Mis empresas / `companies` y Delegaciones / `delegations`), `usuarios`
(con Usuarios empresa / `company-users` y Roles de empresa / `company-roles`), `cuenta`
(Mi cuenta = `/admin/account` + CTA renew; Mis módulos / `company-modules`), `dashboard`
(hoja) y `configuracion` solo vía Module AdminOnly (visible para Super Admin con
excepción de capas), con subítems al menos Módulos, Roles, Permisos, Países,
Monedas, Bancos, Unidades, Tipos de IVA, Grupos contables y Autorización de
módulos. SHALL NOT incluir un ítem de menú genérico «Funcionalidades» (Index
independiente): la gestión de funcionalidades queda anclada a Module/Edit. Mis
empresas SHALL seguir listando solo companies vinculadas; el cambio es de
agrupación bajo Module real, no de perímetro. Delegaciones SHALL listar hijas de la
matriz de sesión. Configuración AdminOnly SHALL ser visible solo para Super Admin (o
actor con capas+AND sobre `configuracion`). SHALL NOT enlazar la ficha Edit del propio
user (`/admin/profile`) desde el menú lateral.

#### Scenario: Mis empresas label
- **WHEN** un usuario con companies vinculadas ve el menú
- **THEN** la entrada al listado de empresas del perímetro se muestra como Mis
  empresas bajo el Module `empresas` (o label equivalente acordado)

#### Scenario: Configuración solo SA
- **WHEN** un usuario no Super Admin obtiene el menú sin capas/permisos AND de
  `configuracion`
- **THEN** no ve el Module Configuración AdminOnly

#### Scenario: SA ve Configuración
- **WHEN** un Super Admin obtiene el menú
- **THEN** ve Configuración como el Module `configuracion` con Módulos, Roles,
  Permisos, Países, Monedas, Bancos, Unidades, Tipos de IVA y Grupos contables,
  y no ve un ítem genérico Funcionalidades ni un bloque sintético ajeno al
  catálogo

#### Scenario: SA ve Configuración como Module
- **WHEN** un Super Admin obtiene el menú
- **THEN** Configuración es el Module `configuracion` con sus Functionalities
  de catálogo, no un bloque sintético

#### Scenario: Ítem Delegaciones bajo Empresas
- **WHEN** el actor tiene acceso al module empresas y a `delegations.view`
- **THEN** el aside muestra el ítem Delegaciones hacia el Index de Delegaciones
