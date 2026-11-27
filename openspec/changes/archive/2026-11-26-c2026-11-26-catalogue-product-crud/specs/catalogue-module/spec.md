# Spec Delta

## Purpose

Registra el Module SaaS `catalogue` (Basic) con la Functionality `products`,
menú Catálogo y acceso HTTP acotado a las tres capas más permisos Spatie.

## ADDED Requirements

### Requirement: Module catalogue y Functionality products
El sistema SHALL registrar el Module slug `catalogue` (level Basic, status
Active) con labels i18n y la Functionality `products`, permisos
`products.{view,viewAny,create,update,delete}` y comodín `catalogue.access`,
conforme a las tres capas SaaS.

#### Scenario: Module Basic autorizado
- **WHEN** una Company matriz tiene el Module `catalogue` autorizado
- **THEN** el módulo queda disponible sin fila `company_modules` (Basic)

#### Scenario: Permisos de products
- **WHEN** se provisionan permisos del Module `catalogue`
- **THEN** existen `products.view`, `products.viewAny`, `products.create`,
  `products.update`, `products.delete` y `catalogue.access`

### Requirement: Menú Catálogo
El sistema SHALL exponer en el menú data-driven un ítem Catálogo
(Functionality `products`) que enlaza `/admin/catalogue`, visible solo con
capas del Module `catalogue` satisfechas y permiso `products.view`.

#### Scenario: Entrada de menú
- **WHEN** un actor con `catalogue` disponible y `products.view` obtiene el menú
- **THEN** ve Catálogo apuntando a `/admin/catalogue`

### Requirement: Rutas bajo módulo activable
Las rutas del catálogo de productos SHALL quedar bajo el gate de módulo
`catalogue` (V-21): módulo off (capas) bloquea; sin permiso → 403;
capas+permiso → OK.

#### Scenario: Módulo no disponible
- **WHEN** las capas SaaS de `catalogue` no están satisfechas para la company
  de sesión y el actor pide `/admin/catalogue`
- **THEN** el acceso queda bloqueado según el contrato de módulo activable

#### Scenario: Sin permiso
- **WHEN** el módulo está disponible pero el actor carece de `products.view`
- **THEN** recibe 403 al pedir el Index
