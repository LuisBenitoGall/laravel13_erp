# Spec Delta — company-hierarchy

## ADDED Requirements

### Requirement: Delegación nace con padre = matriz de sesión
Al crear una Delegación vía el CRUD de Delegaciones, el sistema SHALL asignar
`parent_id` a la company de sesión (matriz) en servidor. SHALL NOT permitir que
el cliente elija otro padre en ese alta. El tab Jerarquía del Edit de Company
SHALL seguir sin crear Companies nuevas (solo albergar/dejar existentes).

#### Scenario: parent_id forzado en alta Delegación
- **WHEN** se crea una Delegación con matriz A en sesión
- **THEN** la nueva Company queda con `parent_id` = A aunque el payload omita
  o intente otro padre
