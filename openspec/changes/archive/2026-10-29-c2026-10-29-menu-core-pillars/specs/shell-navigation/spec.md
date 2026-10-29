## MODIFIED Requirements

### Requirement: Configuración siempre para Super Admin
El sistema SHALL, si el usuario tiene rol Super Admin, incluir en el menú un bloque
Configuración (label i18n) con las funcionalidades de operador AdminOnly prioritarias
para el núcleo (al menos Módulos, Roles y Permisos; Permisos puede compartir destino
temporal con Roles). SHALL NOT exigir que el módulo `admin` esté autorizado y encendido
para la company de sesión. SHALL NOT mostrar el bloque Configuración de operador
AdminOnly a quienes no sean Super Admin. SHALL NOT ampliar el perímetro de datos: solo
companies vinculadas. La entrada de listado de companies del perímetro MAY vivir fuera
de Configuración bajo el pilar Empresas como Mis empresas (solo label/agrupación).

#### Scenario: SA ve Configuración sin módulo admin encendido
- **WHEN** un Super Admin tiene company de sesión cuyo módulo admin no está encendido
- **THEN** el menú incluye Configuración con al menos Módulos, Roles y Permisos

#### Scenario: No SA sin Configuración operador
- **WHEN** un usuario sin rol Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración de operador AdminOnly

## ADDED Requirements

### Requirement: Agrupación Empresas y Usuarios en menú
El sistema SHALL agrupar en el menú las entradas Mis empresas (listado de companies del
perímetro) y Usuarios empresa (Members de la company de sesión, temporal) bajo pilares
Empresas y Usuarios respectivamente, con labels i18n.

#### Scenario: Pilares Empresas y Usuarios visibles
- **WHEN** el menú se construye para un usuario con acceso a esas entradas
- **THEN** aparecen agrupadas/etiquetadas como Empresas / Usuarios según el contrato de
  menu-core-pillars
