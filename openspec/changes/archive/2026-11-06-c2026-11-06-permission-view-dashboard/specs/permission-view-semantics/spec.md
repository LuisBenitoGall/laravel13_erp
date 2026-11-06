## ADDED Requirements

### Requirement: Semántica view vs viewAny
El sistema SHALL tratar Spatie `{functionality}.view` como dashboard/Index/menú
(con comodín de Module) y `{functionality}.viewAny` como detalle (show).
SHALL NOT usar `.viewAny` para dashboard ni menú. Policy Laravel:
`viewAny()`→`.view`, `view()`→`.viewAny` (sin renombrar métodos PHP ni sufijos BD).

#### Scenario: Menú con view
- **WHEN** un usuario tiene `{module}.access` y `{functionality}.view`
- **THEN** la Functionality aparece en el menú

#### Scenario: Menú sin view
- **WHEN** un usuario tiene `{module}.access` y solo `{functionality}.viewAny`
  (sin `.view`)
- **THEN** la Functionality no aparece en el menú por ese permiso

#### Scenario: Index/dashboard exige view
- **WHEN** un usuario sin `{functionality}.view` intenta abrir el Index/dashboard
  de esa Functionality
- **THEN** el acceso se rechaza (403)

#### Scenario: Detalle exige viewAny
- **WHEN** un usuario sin `{functionality}.viewAny` intenta abrir el detalle
  (show) de un registro de esa Functionality
- **THEN** el acceso se rechaza (403), aunque tenga `.view`
