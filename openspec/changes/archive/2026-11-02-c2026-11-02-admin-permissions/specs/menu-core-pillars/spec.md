# Spec Delta

## REMOVED Requirements

### Requirement: Ítem Permisos temporal hacia Roles
**Reason**: Existe el CRUD de catálogo `admin-permission-catalog`; el enlace temporal a
Roles y la prohibición de CRUD custom quedan obsoletos. El contrato canónico es
5 acciones + `{module}.access`, no ×7.
**Migration**: El ítem de menú Permisos enlaza el Index del catálogo Spatie; ver
requisito ADDED «Ítem Permisos hacia catálogo de permisos».

## ADDED Requirements

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
