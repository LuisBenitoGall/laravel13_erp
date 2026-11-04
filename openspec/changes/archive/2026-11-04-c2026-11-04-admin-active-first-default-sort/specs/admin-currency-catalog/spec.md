# Spec Delta

## ADDED Requirements

### Requirement: Default Active-first en Index Monedas
El endpoint `table` de Currencies SHALL, cuando el cliente no envía sorts,
ordenar Active antes que Inactive y, como criterio secundario, por `name`.
SHALL NOT prefijar Active-first cuando el cliente envía sorts.

#### Scenario: Monedas sin sort del cliente
- **WHEN** se solicita el `table` de currencies sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo de status, por name

#### Scenario: Monedas con sort del cliente
- **WHEN** el cliente envía un sort (p. ej. por code)
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio
