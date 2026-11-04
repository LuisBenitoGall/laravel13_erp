# Spec Delta

## MODIFIED Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú los pilares Empresas (con Mis empresas), Usuarios
(con Usuarios empresa) y Configuración solo para Super Admin, con subítems al menos
Módulos, Roles, Permisos, Países, Monedas, Bancos, Unidades, Tipos de IVA y Grupos
contables. SHALL NOT incluir un ítem de menú genérico «Funcionalidades» (Index
independiente): la gestión de funcionalidades queda anclada a Module/Edit. Mis empresas
SHALL seguir listando solo companies vinculadas; el cambio es de label/agrupación, no de
perímetro. Configuración AdminOnly SHALL ser visible solo para Super Admin.

#### Scenario: Mis empresas label
- **WHEN** un usuario con companies vinculadas ve el menú
- **THEN** la entrada al listado de empresas del perímetro se muestra como Mis empresas
  bajo el pilar Empresas (o label equivalente acordado)

#### Scenario: Configuración solo SA
- **WHEN** un usuario no Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración AdminOnly

#### Scenario: SA ve Configuración
- **WHEN** un Super Admin obtiene el menú
- **THEN** ve Configuración con Módulos, Roles, Permisos, Países, Monedas, Bancos,
  Unidades, Tipos de IVA y Grupos contables, y no ve un ítem genérico Funcionalidades

## ADDED Requirements

### Requirement: Ítem Grupos contables en Configuración
El sistema SHALL incluir bajo Configuración un ítem Grupos contables (Functionality
`accounting-account-types`) que enlaza el Index del catálogo AccountingAccountType.

#### Scenario: SA abre Grupos contables desde el menú
- **WHEN** Super Admin abre Grupos contables desde Configuración
- **THEN** navega al Index de accounting-account-types
