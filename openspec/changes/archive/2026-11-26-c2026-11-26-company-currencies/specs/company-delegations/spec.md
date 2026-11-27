# Spec Delta

## ADDED Requirements

### Requirement: Sección Monedas en Edit Delegación
El tab Información del Edit de Delegación SHALL incluir una sección Monedas
(default + permitidas) con la misma semántica que Edit Company, independiente
de la matriz. SHALL NOT añadir un tab dedicado Monedas. Create Delegación
SHALL NOT exigir monedas.

#### Scenario: Sección en Información
- **WHEN** un actor autorizado abre Edit Delegación
- **THEN** ve Monedas dentro de Información junto a los datos de identidad

#### Scenario: Default propio de la Delegación
- **WHEN** el actor guarda default USD en una Delegación cuya matriz tiene
  default EUR
- **THEN** la Delegación queda con default USD sin alterar la matriz
