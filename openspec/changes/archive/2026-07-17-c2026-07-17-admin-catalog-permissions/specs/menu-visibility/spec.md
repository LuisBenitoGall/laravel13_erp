## ADDED Requirements

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un `Module` en el menú si el usuario tiene su permiso comodín de
módulo o al menos un permiso de alguna de sus Functionalities, y SHALL mostrar cada
`Functionality` si el usuario tiene el comodín de su módulo o su propio permiso
`viewAny` — nunca exigiendo ambos a la vez (R-AUT-08). En esta iteración NO se exige
`CompanyModules` activo (aún no existe `companies`); ver excepción en el design del
change.

#### Scenario: Módulo visible por permiso de Functionality sin comodín
- **WHEN** un usuario tiene el permiso `viewAny` de una `Functionality` pero no el comodín
  `<module-slug>.access` de su módulo
- **THEN** el módulo aparece en el menú con esa `Functionality` visible

#### Scenario: Comodín de módulo implica todas sus Functionalities
- **WHEN** un usuario tiene el permiso comodín `<module-slug>.access`
- **THEN** ve todas las Functionalities de ese módulo en el submenú, tenga o no permisos
  individuales sobre cada una
