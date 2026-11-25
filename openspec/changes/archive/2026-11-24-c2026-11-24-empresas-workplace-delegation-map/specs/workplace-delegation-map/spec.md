# Spec Delta — workplace-delegation-map

## ADDED Requirements

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
