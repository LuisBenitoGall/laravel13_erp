# Spec Delta — workplace-delegation-map

## ADDED Requirements

### Requirement: Pivote como única relación Delegación–Workplace
La asociación entre Delegación y Workplace SHALL ser exclusivamente el pivote
N:N `workplace_delegation` (con preferida opcional). SHALL NOT modelarse
asignando `workplaces.company_id` a la Delegación.

#### Scenario: Un workplace varias delegaciones
- **WHEN** desde Centros de trabajo se vincula un workplace de la matriz a D1
  y D2
- **THEN** ambas asociaciones persisten (N:N intacto)

## MODIFIED Requirements

### Requirement: Lectura inversa del mapeo desde Edit Delegación
El sistema SHALL exponer, para la ficha Edit de una Delegación, la lista de
Workplaces de su matriz que la tienen vinculada en el pivote N:N (incl.
`is_preferred`), en solo lectura, como **único** listado de centros de esa
ficha. SHALL NOT alterar la regla de escritura del mapeo (sigue en Edit
Workplace con `workplaces.update`).

#### Scenario: Datos coherentes con el pivote
- **WHEN** existe fila `workplace_delegation` (workplace de matriz A, company =
  Delegación D) y se abre el Edit de D en sesión A
- **THEN** esa sede figura en la proyección RO de D
