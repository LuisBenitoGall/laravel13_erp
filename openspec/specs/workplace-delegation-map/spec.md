## Purpose

Mapeo N:M entre Workplaces de la matriz de sesión y Delegaciones hijas, con
como máximo una delegación preferida por sede.

## Requirements

### Requirement: Mapeo N:M Workplace–Delegación
El sistema SHALL permitir vincular cada Workplace de la matriz de sesión a cero
o más Delegaciones (Companies con `parent_id` = esa matriz), y cada Delegación
a cero o más de esos Workplaces. SHALL NOT permitir vincular workplaces ajenos
a la sesión ni companies que no sean hijas de la matriz del workplace.

#### Scenario: Vincular sede a varias delegaciones
- **WHEN** un actor autorizado asocia un workplace de la matriz A a las
  delegaciones D1 y D2 (hijas de A)
- **THEN** quedan persistidos ambos vínculos

#### Scenario: Perímetro
- **WHEN** se intenta vincular un workplace o una company fuera de la matriz
  de sesión / no hija de esa matriz
- **THEN** se rechaza

### Requirement: Delegación preferida por Workplace
El sistema SHALL permitir como máximo una Delegación marcada como preferida por
Workplace. SHALL NOT exigir que exista preferida.

#### Scenario: Una preferida
- **WHEN** el actor marca D2 como preferida en un workplace que ya tenía D1
  preferida
- **THEN** solo D2 queda preferida

#### Scenario: Sin preferida
- **WHEN** un workplace tiene vínculos pero ninguna preferida
- **THEN** el estado es válido

### Requirement: Pivote como única relación Delegación–Workplace
La asociación entre Delegación y Workplace SHALL ser exclusivamente el pivote
N:N `workplace_delegation` (con preferida opcional). SHALL NOT modelarse
asignando `workplaces.company_id` a la Delegación.

#### Scenario: Un workplace varias delegaciones
- **WHEN** desde Centros de trabajo se vincula un workplace de la matriz a D1
  y D2
- **THEN** ambas asociaciones persisten (N:N intacto)

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

### Requirement: Sede preferida por Delegación en el pivote
El pivote `workplace_delegation` SHALL soportar, además de la preferida por
Workplace, una marca de sede preferida por Delegación (como máximo una por
Delegación, opcional). SHALL NOT reutilizar ni reinterpretar `is_preferred`
para ese eje.

#### Scenario: Independencia de ejes
- **WHEN** una sede W está vinculada a D con `is_preferred` true y luego se
  marca otra sede como preferida de D
- **THEN** `is_preferred` de W respecto a D permanece true
