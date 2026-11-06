## Purpose

Pilares de menú como Modules del catálogo: `empresas` (Mis empresas), `usuarios`
(Usuarios empresa → Index ServerTable; Todos los usuarios; company-roles),
`cuenta` (Mi cuenta / Mis módulos), `dashboard` (hoja) y `configuracion`
(SA AdminOnly: Módulos, Roles, Permisos, Países, Monedas, Bancos, Unidades,
Tipos de IVA, Grupos contables, Autorizaciones). Sin bloques sintéticos ni
slugs `admin`/`workspace`.

## Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares como Modules del catálogo:
`empresas` (con Mis empresas / `companies`), `usuarios` (con Usuarios empresa /
`company-users` y Roles de empresa / `company-roles`), `cuenta` (Mi cuenta /
`profile` y Mis módulos / `company-modules`), `dashboard` (hoja) y
`configuracion` solo vía Module AdminOnly (visible para Super Admin con
excepción de capas), con subítems al menos Módulos, Roles, Permisos, Países,
Monedas, Bancos, Unidades, Tipos de IVA, Grupos contables y Autorización de
módulos. SHALL NOT incluir un ítem de menú genérico «Funcionalidades» (Index
independiente): la gestión de funcionalidades queda anclada a Module/Edit. Mis
empresas SHALL seguir listando solo companies vinculadas; el cambio es de
agrupación bajo Module real, no de perímetro. Configuración AdminOnly SHALL ser
visible solo para Super Admin (o actor con capas+AND sobre `configuracion`).

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

### Requirement: Ítem Países en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Países
(Functionality `countries`) que enlaza el Index del catálogo geográfico Admin.

#### Scenario: SA abre Países desde el menú
- **WHEN** Super Admin abre Países desde Configuración
- **THEN** navega al Index de countries

### Requirement: Ítem Monedas en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Monedas
(Functionality `currencies`) que enlaza el Index del catálogo de monedas Admin.

#### Scenario: SA abre Monedas desde el menú
- **WHEN** Super Admin abre Monedas desde Configuración
- **THEN** navega al Index de currencies

### Requirement: Ítem Bancos en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Bancos
(Functionality `banks`) que enlaza el Index del catálogo de bancos (dominio
Banks).

#### Scenario: SA abre Bancos desde el menú
- **WHEN** Super Admin abre Bancos desde Configuración
- **THEN** navega al Index de banks

### Requirement: Ítem Unidades en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Unidades
(Functionality `units`) que enlaza el Index del catálogo de unidades Admin.

#### Scenario: SA abre Unidades desde el menú
- **WHEN** Super Admin abre Unidades desde Configuración
- **THEN** navega al Index de units

### Requirement: Ítem Tipos de IVA en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Tipos de IVA
(Functionality `vat-types`) que enlaza el Index del catálogo VatType Admin.

#### Scenario: SA abre Tipos de IVA desde el menú
- **WHEN** Super Admin abre Tipos de IVA desde Configuración
- **THEN** navega al Index de vat-types

### Requirement: Ítem Grupos contables en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Grupos contables
(Functionality `accounting-account-types`) que enlaza el Index del catálogo
AccountingAccountType.

#### Scenario: SA abre Grupos contables desde el menú
- **WHEN** Super Admin abre Grupos contables desde Configuración
- **THEN** navega al Index de accounting-account-types

### Requirement: Ítem Permisos hacia catálogo de permisos
El sistema SHALL incluir un ítem de menú Permisos distinto de Roles bajo el
Module `configuracion`. Ese ítem SHALL enlazar el Index Admin del catálogo
Spatie de permisos. El set canónico auto-provisionado por Functionality SHALL
ser exactamente `viewAny`/`view`/`create`/`update`/`delete` más el comodín
`{module}.access`.

#### Scenario: Permisos abre el catálogo
- **WHEN** Super Admin abre Permisos desde el menú Configuración
- **THEN** navega al Index de permisos Spatie (no a Roles)

#### Scenario: Contrato de acciones canónicas
- **WHEN** se documenta o valida el set auto-provisionado por Functionality
- **THEN** son exactamente las cinco acciones anteriores más el comodín de
  módulo `access` (no un set de siete)

### Requirement: Menú Usuarios con Usuarios empresa y Todos
El sistema SHALL exponer bajo el Module `usuarios` las entradas de menú
«Usuarios empresa» (Functionality `company-users` → Index Usuarios empresa) y
«Todos los usuarios» (Functionality de directorio global). Este change SHALL NOT
exigir la entrada Roles de empresa bajo Usuarios ni bajo Empresas.

#### Scenario: Submenú Usuarios
- **WHEN** un usuario con acceso al Module Usuarios obtiene el menú
- **THEN** ve Usuarios empresa y Todos los usuarios

#### Scenario: Usuarios empresa abre Index company-users
- **WHEN** un usuario con company de sesión abre Usuarios empresa
- **THEN** navega al Index ServerTable de Usuarios empresa (no al Members HTML
  legacy como destino de menú)

### Requirement: Cuenta con Mi cuenta y Mis módulos
El sistema SHALL exponer bajo el Module `cuenta` las Functionalities `profile`
(Mi cuenta → ruta profile existente) y `company-modules` (Mis módulos → Index
company-modules existente). SHALL NOT rediseñar tiles de company-modules en este
change. La visibilidad de menú de esas Functionalities SHALL exigir
`cuenta.access` **y** el permiso Spatie `.view` de cada Functionality
(`profile.view`, `company-modules.view`).

#### Scenario: Mis módulos bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `company-modules.view` y capas
  company satisfechas obtiene el menú
- **THEN** ve Mis módulos bajo Cuenta y navega al Index company-modules

#### Scenario: Mi cuenta bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `profile.view` obtiene el menú
- **THEN** ve Mi cuenta bajo Cuenta y navega a la pantalla profile existente
