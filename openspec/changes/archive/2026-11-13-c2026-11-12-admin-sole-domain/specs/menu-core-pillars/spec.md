# Spec Delta

## ADDED Requirements

### Requirement: Mis empresas apunta a /admin/companies
El menú SHALL enlazar Mis empresas a la ruta Index bajo `/admin/companies`
(`admin.companies.index`). SHALL NOT usar `partners.companies` ni
`/partners/companies`.

#### Scenario: Href Mis empresas
- **WHEN** el menú construye el href de Mis empresas
- **THEN** apunta a `/admin/companies` (ruta admin)

## MODIFIED Requirements

### Requirement: Cuenta con Mi cuenta y Mis módulos
El sistema SHALL exponer bajo el Module `cuenta` las Functionalities de Mi
cuenta (slug actual `profile`: destino placeholder cuenta SaaS company,
próximamente) y `company-modules` (Mis módulos → Index company-modules
existente). SHALL NOT rediseñar tiles de company-modules en este change. SHALL
NOT usar Mi cuenta como enlace a la ficha Edit del propio user
(`/admin/profile`). La ficha self SHALL quedar fuera del menú lateral (acceso
vía avatar). La visibilidad de menú de esas Functionalities SHALL exigir
`cuenta.access` **y** el permiso Spatie `.view` de cada Functionality
(`profile.view`, `company-modules.view`).

#### Scenario: Mis módulos bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `company-modules.view` y capas
  company satisfechas obtiene el menú
- **THEN** ve Mis módulos bajo Cuenta y navega al Index company-modules

#### Scenario: Mi cuenta bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `profile.view` obtiene el menú
- **THEN** ve Mi cuenta bajo Cuenta como próximamente (cuenta company) y no
  navega a la ficha Edit del propio user

### Requirement: Profile ficha solo desde avatar; sin activo en lateral
La ficha `/admin/profile` SHALL ser accesible desde el menú del avatar del
topbar. El menú lateral SHALL NOT incluir un href a `/admin/profile`. Mientras
la URL sea `/admin/profile` o subruta de esa ficha self, el AsideNav SHALL NOT
marcar ningún ítem como activo (`aria-current`).

#### Scenario: Avatar abre profile
- **WHEN** el usuario elige Perfil en el desplegable del avatar
- **THEN** navega a `/admin/profile`

#### Scenario: Lateral sin href profile
- **WHEN** se construye el menú lateral
- **THEN** ningún ítem enlaza a `/admin/profile`

#### Scenario: Profile sin highlight
- **WHEN** el actor está en `/admin/profile` (o subruta de la ficha self)
- **THEN** ningún ítem del lateral tiene estado activo / `aria-current`
