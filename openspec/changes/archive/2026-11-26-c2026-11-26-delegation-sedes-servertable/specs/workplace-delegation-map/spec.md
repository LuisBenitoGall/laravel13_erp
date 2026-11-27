# Spec Delta

## ADDED Requirements

### Requirement: Sede preferida por Delegación en el pivote
El pivote `workplace_delegation` SHALL soportar, además de la preferida por
Workplace, una marca de sede preferida por Delegación (como máximo una por
Delegación, opcional). SHALL NOT reutilizar ni reinterpretar `is_preferred`
para ese eje.

#### Scenario: Independencia de ejes
- **WHEN** una sede W está vinculada a D con `is_preferred` true y luego se
  marca otra sede como preferida de D
- **THEN** `is_preferred` de W respecto a D permanece true

## MODIFIED Requirements

### Requirement: Lectura inversa del mapeo desde Edit Delegación
El sistema SHALL exponer, para la ficha Edit de una Delegación, los
Workplaces de su matriz vinculados en el pivote N:N mediante `<ServerTable>`
(columnas de ubicación, fecha de creación y sede preferida de la
Delegación), como **único** listado de centros de esa ficha. SHALL NOT
alterar la regla de escritura del mapeo N:N (sigue en Edit Workplace con
`workplaces.update`). SHALL NOT exigir mostrar en esa tabla la columna de
preferida-por-sede.

#### Scenario: Datos coherentes con el pivote
- **WHEN** existe fila `workplace_delegation` (workplace de matriz A, company =
  Delegación D) y se abre el Edit de D en sesión A
- **THEN** esa sede figura en la ServerTable de D
