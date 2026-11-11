## MODIFIED Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares como Modules del catálogo:
`empresas` (con Mis empresas / `companies`), `usuarios` (con Usuarios empresa /
`company-users` y Roles de empresa / `company-roles`), `cuenta` (Mi cuenta =
cuenta SaaS company / placeholder; Mis módulos / `company-modules`), `dashboard`
(hoja) y `configuracion` solo vía Module AdminOnly (visible para Super Admin con
excepción de capas), con subítems al menos Módulos, Roles, Permisos, Países,
Monedas, Bancos, Unidades, Tipos de IVA, Grupos contables y Autorización de
módulos. SHALL NOT incluir un ítem de menú genérico «Funcionalidades» (Index
independiente): la gestión de funcionalidades queda anclada a Module/Edit. Mis
empresas SHALL seguir listando solo companies vinculadas; el cambio es de
agrupación bajo Module real, no de perímetro. Configuración AdminOnly SHALL ser
visible solo para Super Admin (o actor con capas+AND sobre `configuracion`).
SHALL NOT enlazar la ficha Edit del propio user (`/profile`) desde el menú
lateral.

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

### Requirement: Cuenta con Mi cuenta y Mis módulos
El sistema SHALL exponer bajo el Module `cuenta` las Functionalities de Mi
cuenta (slug actual `profile`: destino placeholder cuenta SaaS company,
próximamente) y `company-modules` (Mis módulos → Index company-modules
existente). SHALL NOT rediseñar tiles de company-modules en este change. SHALL
NOT usar Mi cuenta como enlace a la ficha Edit del propio user (`/profile`).
La ficha self SHALL quedar fuera del menú lateral (acceso vía avatar). La
visibilidad de menú de esas Functionalities SHALL exigir `cuenta.access` **y**
el permiso Spatie `.view` de cada Functionality (`profile.view`,
`company-modules.view`).

#### Scenario: Mis módulos bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `company-modules.view` y capas
  company satisfechas obtiene el menú
- **THEN** ve Mis módulos bajo Cuenta y navega al Index company-modules

#### Scenario: Mi cuenta bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `profile.view` obtiene el menú
- **THEN** ve Mi cuenta bajo Cuenta como próximamente (cuenta company) y no
  navega a la ficha Edit del propio user

## ADDED Requirements

### Requirement: Profile ficha solo desde avatar; sin activo en lateral
La ficha `/profile` SHALL ser accesible desde el menú del avatar del topbar.
El menú lateral SHALL NOT incluir un href a `/profile`. Mientras la URL sea
`/profile` o subruta de esa ficha self, el AsideNav SHALL NOT marcar ningún
ítem como activo (`aria-current`).

#### Scenario: Avatar abre profile
- **WHEN** el usuario elige Perfil en el desplegable del avatar
- **THEN** navega a `/profile`

#### Scenario: Lateral sin href profile
- **WHEN** se construye el menú lateral
- **THEN** ningún ítem enlaza a `/profile`

#### Scenario: Profile sin highlight
- **WHEN** el actor está en `/profile` (o subruta de la ficha self)
- **THEN** ningún ítem del lateral tiene estado activo / `aria-current`
