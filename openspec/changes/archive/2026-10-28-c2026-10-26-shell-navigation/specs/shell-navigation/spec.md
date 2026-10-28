## ADDED Requirements

### Requirement: Aside con logos y Dashboard fijo
El sistema SHALL mostrar en el aside del `AppLayout`, en este orden: (1) espacio para el
logo del ERP, (2) espacio para el logo de la company de sesión si existe (vacío si no hay
logo), y (3) el menú de navegación. SHALL incluir siempre un ítem Dashboard (enlace a la
ruta dashboard existente o placeholder mínimo) para cualquier usuario autenticado con
acceso al ERP, aunque no haya otros módulos elegibles. SHALL NOT inventar entradas a
Tienda, Consultas ni maestros Fase 1 aún no implementados.

#### Scenario: Orden del aside
- **WHEN** un usuario entra al ERP con AppLayout
- **THEN** ve zona logo ERP, zona logo empresa (o vacío si no hay logo), y debajo el menú

#### Scenario: Dashboard siempre
- **WHEN** el menú se construye para un usuario con sesión de empresa (o acceso ERP)
- **THEN** existe la entrada Dashboard aunque no haya otros módulos elegibles

---

### Requirement: Configuración siempre para Super Admin
El sistema SHALL, si el usuario tiene rol Super Admin, incluir en el menú un bloque
Configuración (label i18n acordado) con las funcionalidades de operador / AdminOnly del
catálogo activo (p. ej. modules, functionalities, companies, company-modules,
company-module-authorizations, roles de plataforma), sin exigir que el módulo `admin`
esté autorizado y encendido para la company de sesión. SHALL unificar bajo ese bloque el
agrupado previo «Plataforma». SHALL NOT mostrar el bloque Configuración de operador
AdminOnly a usuarios que no sean Super Admin. SHALL NOT ampliar el perímetro de datos:
solo companies vinculadas.

#### Scenario: SA ve Configuración sin módulo admin encendido
- **WHEN** un Super Admin tiene company de sesión cuyo módulo admin no está encendido
- **THEN** el menú incluye Configuración con los submenús operador existentes

#### Scenario: No SA sin Configuración operador
- **WHEN** un usuario sin rol Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración de operador AdminOnly

---

### Requirement: Menú tenant según capas (salvo excepciones de shell)
El sistema SHALL seguir construyendo el resto del menú (no Dashboard, no Configuración
SA) según módulos Active + autorizados + encendidos y permisos del usuario
(Basic/Optional), alineado a R-AUT-08. SHALL agrupar o etiquetar de forma coherente con
cuenta / empresa donde haya funcionalidades existentes (p. ej. company-roles bajo
empresa), sin inventar enlaces a maestros aún no implementados.

#### Scenario: Tenant ve ítems de módulos elegibles
- **WHEN** un manager tiene un módulo Basic/Optional autorizado y encendido y permiso
  `viewAny` (o comodín)
- **THEN** esos ítems aparecen en el menú además de Dashboard
