# Spec Delta

## MODIFIED Requirements

### Requirement: Cuenta con Mi cuenta y Mis módulos
El sistema SHALL exponer bajo el Module `cuenta` las Functionalities de Mi
cuenta (slug actual `profile`: destino `/admin/account` con histórico de
`company_accounts` de la empresa en sesión y CTA a renovación/upgrade) y
`company-modules` (Mis módulos → Index company-modules existente). SHALL
NOT rediseñar tiles de company-modules en este change. SHALL NOT usar Mi
cuenta como enlace a la ficha Edit del propio user (`/admin/profile`). La
ficha self SHALL quedar fuera del menú lateral (acceso vía avatar). La
visibilidad de menú de esas Functionalities SHALL exigir `cuenta.access`
**y** el permiso Spatie `.view` de cada Functionality (`profile.view`,
`company-modules.view`).

#### Scenario: Mis módulos bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `company-modules.view` y capas
  company satisfechas obtiene el menú
- **THEN** ve Mis módulos bajo Cuenta y navega al Index company-modules

#### Scenario: Mi cuenta bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `profile.view` obtiene el menú
- **THEN** ve Mi cuenta bajo Cuenta, navega a `/admin/account` (histórico
  de la cuenta SaaS de la empresa) y no navega a la ficha Edit del propio
  user
