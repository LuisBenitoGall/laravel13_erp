# Spec Delta

## ADDED Requirements

### Requirement: Index con filtros avanzados y presets
El Index de productos SHALL montar el kit de filtros avanzados
(toolbar + modal + presets) con campos al menos para nombre, tipo,
estado, familia, categoría y etiqueta, usando el canal `filter` en modo
`merge`. El endpoint table SHALL aceptar esos campos en la whitelist
filterable. Un actor autorizado SHALL poder limpiar, aplicar y
guardar/cargar presets según `table-filter-presets`.

#### Scenario: Products abre filtros
- **WHEN** un actor autorizado abre el Index de productos y el modal de
  filtros avanzados
- **THEN** ve los campos declarados y puede limpiar, aplicar y gestionar
  presets

#### Scenario: Filtro por taxonomía en table
- **WHEN** el actor aplica filtros avanzados de familia, categoría o
  etiqueta
- **THEN** el listado remoto solo incluye productos que cumplan esos
  filtros dentro de la company de sesión
