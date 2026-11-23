# Spec Delta — hr-employees

## ADDED Requirements

### Requirement: No solape calendario vs scheduled y active
En alta o renovación de un periodo en una company, el nuevo intervalo
(`[start_date`, `end_date` o abierto]) SHALL NOT solapar con ningún periodo
`scheduled` o `active` del mismo user en esa company, salvo los `active` que la
propia operación cierra en la misma transacción. SHALL NOT aplicarse entre
companies distintas. Periodos `ended` SHALL NOT participar en la regla.

#### Scenario: Dos scheduled solapados rechazados
- **WHEN** existe un periodo `scheduled` 25/11/2026→∞ y se intenta crear otro
  `scheduled` 24/11/2026→10/01/2027 en la misma company para el mismo user
- **THEN** se rechaza la operación

#### Scenario: Inicio el día siguiente al fin de un scheduled OK
- **WHEN** existe un `scheduled` o `active` con fin 24/11/2026 y el nuevo inicia
  el 25/11/2026 sin solapar con otros no-ended
- **THEN** la operación puede aceptarse (resto de reglas OK)

#### Scenario: Solape entre companies permitido
- **WHEN** el user tiene un scheduled/active en company A y se crea un periodo
  en company B con fechas solapadas
- **THEN** no se rechaza por la regla de solape de A

#### Scenario: Create/Renew que cierra active no se autobloquea
- **WHEN** se renueva un active (o se crea un periodo que nace active) y el
  active previo solapa en calendario con el nuevo
- **THEN** el previo se cierra (ended; indefinido con fin D−1) y el nuevo se
  crea; el assert no rechaza por ese active cerrado en la misma operación

## MODIFIED Requirements

### Requirement: No solape con el active de la misma company
La regla de no solape SHALL aplicar solape de intervalos frente a periodos
`scheduled` y `active` del mismo user en la misma company (opción B). Los
`active` que la propia operación cierra (Renew del current, o Create cuando el
nuevo nace `active`) SHALL NOT bloquear el assert. Periodos `ended` y otras
companies SHALL NOT participar. Renovar indefinido con fin D−1 se mantiene.

#### Scenario: Solape rechazado misma company
- **WHEN** el active de la company termina el 24/11/2026 y se intenta un nuevo
  periodo con inicio ≤ 24/11/2026 (y no es el active cerrado por esta operación)
- **THEN** se rechaza la operación

#### Scenario: Inicio al día siguiente OK
- **WHEN** el active termina el 24/11/2026 y el nuevo inicia el 25/11/2026
- **THEN** la operación puede aceptarse (resto de reglas OK)

#### Scenario: Solape entre companies permitido
- **WHEN** el user tiene un active en company A y se crea un periodo en
  company B con fechas solapadas
- **THEN** no se rechaza por la regla de solape de A

#### Scenario: Solape rechazado misma company (ampliado)
- **WHEN** hay un periodo no-ended (scheduled o active) que solapa con el nuevo
  en la misma company
- **THEN** se rechaza, salvo que ese periodo sea un active cerrado por esta
  misma operación
