# Spec Delta

## ADDED Requirements

### Requirement: Indexes geográficos sin columna Estado
Los Indexes ServerTable de Países, Provincias y Poblaciones SHALL NOT mostrar
columna de estado textual cuando el status se gestiona con toggle en Acciones.
Los formularios Create/Edit MAY seguir incluyendo el campo status.

#### Scenario: Index Países sin Estado
- **WHEN** se carga el Index de Países
- **THEN** no hay columna Estado y el toggle de Acciones permanece

#### Scenario: Indexes Provincias y Poblaciones sin Estado
- **WHEN** se cargan los Indexes de Provincias o Poblaciones
- **THEN** tampoco muestran columna Estado

### Requirement: Iconos drill Map / MapPin distintos de Edit
En Acciones del Index de Países, la acción de provincias SHALL usar Lucide `Map`.
En Acciones del Index de Provincias, la acción de poblaciones SHALL usar Lucide
`MapPin`. Edit SHALL usar `Pencil` en ambos.

#### Scenario: País — Map vs Pencil
- **WHEN** se muestran Acciones de una fila de país
- **THEN** provincias usa Map y editar usa Pencil

#### Scenario: Provincia — MapPin vs Pencil
- **WHEN** se muestran Acciones de una fila de provincia
- **THEN** poblaciones usa MapPin y editar usa Pencil
