# Spec Delta — company-delegations

## ADDED Requirements

### Requirement: Delegaciones elegibles en mapeo de sedes
Las Companies hijas (Delegaciones) de la matriz de sesión SHALL ser el conjunto
elegible para el mapeo N:M Workplace–Delegación. SHALL NOT exigir cambios al
CRUD de Delegaciones más allá de ser seleccionables en ese perímetro.

#### Scenario: Solo hijas de la matriz en el selector
- **WHEN** el actor edita el mapeo de un workplace de la matriz A
- **THEN** solo puede elegir Companies con `parent_id` = A
