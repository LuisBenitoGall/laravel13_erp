## MODIFIED Requirements

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
