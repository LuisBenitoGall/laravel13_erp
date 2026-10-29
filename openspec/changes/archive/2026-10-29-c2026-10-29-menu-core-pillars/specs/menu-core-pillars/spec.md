## ADDED Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares Empresas (con Mis empresas), Usuarios
(con Usuarios empresa) y Configuración solo para Super Admin, con subítems al menos
Módulos, Roles y Permisos. Mis empresas SHALL seguir listando solo companies vinculadas;
el cambio es de label/agrupación, no de perímetro. Configuración AdminOnly SHALL ser
visible solo para Super Admin.

#### Scenario: Mis empresas label
- **WHEN** un usuario con companies vinculadas ve el menú
- **THEN** la entrada al listado de empresas del perímetro se muestra como Mis empresas
  bajo el pilar Empresas (o label equivalente acordado)

#### Scenario: Configuración solo SA
- **WHEN** un usuario no Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración AdminOnly

#### Scenario: SA ve Configuración
- **WHEN** un Super Admin obtiene el menú
- **THEN** ve Configuración con Módulos, Roles y Permisos

---

### Requirement: Ítem Permisos temporal hacia Roles
El sistema SHALL incluir un ítem de menú Permisos distinto de Roles. Mientras no exista
el CRUD propio de permisos, ese ítem SHALL enlazar a la UI de Roles existente (índice /
flujo donde se gestiona la matriz de permisos). SHALL NOT implementar el CRUD de
permisos custom al margen de funcionalidades×7 acciones.

#### Scenario: Permisos abre Roles
- **WHEN** Super Admin abre Permisos desde el menú
- **THEN** navega a la experiencia actual de Roles (matriz vía esa UI), sin pantalla
  nueva de permisos

---

### Requirement: Usuarios empresa temporal hacia Members
El sistema SHALL incluir la entrada Usuarios empresa sin pantalla nueva. SHALL enlazarla
de forma temporal al listado de miembros de la company de sesión (ruta Members
existente).

#### Scenario: Usuarios empresa abre Members de sesión
- **WHEN** un usuario con company de sesión abre Usuarios empresa
- **THEN** navega al índice de miembros de esa company
