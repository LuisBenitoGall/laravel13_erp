# Spec Delta — workplace-delegation-map

## ADDED Requirements

### Requirement: Lectura inversa del mapeo desde Edit Delegación
El sistema SHALL exponer, para la ficha Edit de una Delegación, la lista de
Workplaces de su matriz que la tienen vinculada en el pivote N:M (incl.
`is_preferred`), en solo lectura. SHALL NOT alterar la regla de escritura del
mapeo (sigue en Edit Workplace con `workplaces.update`).

#### Scenario: Datos coherentes con el pivote
- **WHEN** existe fila `workplace_delegation` (workplace de matriz A, company =
  Delegación D) y se abre el Edit de D en sesión A
- **THEN** esa sede figura en la proyección RO de D
