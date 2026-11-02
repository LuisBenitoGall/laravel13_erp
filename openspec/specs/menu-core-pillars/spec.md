## Purpose

Pilares de menú Empresas (Mis empresas), Usuarios (Usuarios empresa → Members) y
Configuración SA (Módulos, Roles, Permisos → catálogo Spatie). Labels/agrupación;
CRUD de permisos custom vive en `admin-permission-catalog`.

## Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares Empresas (con Mis empresas), Usuarios
(con Usuarios empresa) y Configuración solo para Super Admin, con subítems al menos
Módulos, Roles y Permisos. SHALL NOT incluir un ítem de menú genérico «Funcionalidades»
(Index independiente): la gestión de funcionalidades queda anclada a Module/Edit. Mis
empresas SHALL seguir listando solo companies vinculadas; el cambio es de
label/agrupación, no de perímetro. Configuración AdminOnly SHALL ser visible solo para
Super Admin.

#### Scenario: Mis empresas label
- **WHEN** un usuario con companies vinculadas ve el menú
- **THEN** la entrada al listado de empresas del perímetro se muestra como Mis empresas
  bajo el pilar Empresas (o label equivalente acordado)

#### Scenario: Configuración solo SA
- **WHEN** un usuario no Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración AdminOnly

#### Scenario: SA ve Configuración
- **WHEN** un Super Admin obtiene el menú
- **THEN** ve Configuración con Módulos, Roles y Permisos, y no ve un ítem genérico
  Funcionalidades

### Requirement: Ítem Permisos hacia catálogo de permisos
El sistema SHALL incluir un ítem de menú Permisos distinto de Roles bajo Configuración.
Ese ítem SHALL enlazar el Index Admin del catálogo Spatie de permisos. El set canónico
auto-provisionado por Functionality SHALL ser exactamente
`viewAny`/`view`/`create`/`update`/`delete` más el comodín `{module}.access`.

#### Scenario: Permisos abre el catálogo
- **WHEN** Super Admin abre Permisos desde el menú Configuración
- **THEN** navega al Index de permisos Spatie (no a Roles)

#### Scenario: Contrato de acciones canónicas
- **WHEN** se documenta o valida el set auto-provisionado por Functionality
- **THEN** son exactamente las cinco acciones anteriores más el comodín de módulo
  `access` (no un set de siete)

### Requirement: Usuarios empresa temporal hacia Members
El sistema SHALL incluir la entrada Usuarios empresa sin pantalla nueva. SHALL enlazarla
de forma temporal al listado de miembros de la company de sesión (ruta Members
existente).

#### Scenario: Usuarios empresa abre Members de sesión
- **WHEN** un usuario con company de sesión abre Usuarios empresa
- **THEN** navega al índice de miembros de esa company
