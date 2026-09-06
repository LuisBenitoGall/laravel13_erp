## ADDED Requirements

### Requirement: Catálogo Module y Functionality
El sistema SHALL mantener un catálogo global de `Module` (unidad SaaS-activable) y
`Functionality` (unidad de permiso y menú dentro de un módulo), con slug de
`Functionality` inmutable tras crearse (R-AUT-07).

#### Scenario: Alta de Functionality provisiona sus permisos
- **WHEN** se crea una `Functionality` nueva bajo un `Module`
- **THEN** se provisionan automáticamente sus 5 permisos
  (`viewAny`/`view`/`create`/`update`/`delete`) con prefijo igual al slug de la
  `Functionality`

#### Scenario: Slug de Functionality inmutable
- **WHEN** se edita una `Functionality` existente
- **THEN** solo se puede modificar `label`, `status` y `module_id` — el slug no es
  editable

#### Scenario: Module con campos de catálogo
- **WHEN** se crea o edita un `Module`
- **THEN** acepta `name`, `slug`, `label` (traducible), `color`, `icon`, `level`
  (`ModuleLevel`: AdminOnly/Basic/Optional), `status` (`ModuleStatus`: Active/Inactive) y
  `explanation` (traducible)
