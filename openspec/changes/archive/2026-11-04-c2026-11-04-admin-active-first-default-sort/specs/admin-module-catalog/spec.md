# Spec Delta

## ADDED Requirements

### Requirement: Default Active-first en Index Modules
El endpoint `table` de Modules SHALL, cuando el cliente no envía sorts, ordenar
los registros con status Active antes que Inactive. SHALL NOT exigir un criterio
secundario adicional. SHALL NOT prefijar Active-first cuando el cliente envía
sorts.

#### Scenario: Modules sin sort del cliente
- **WHEN** se solicita el `table` de modules sin sorts
- **THEN** aparecen primero los Active y después los Inactive

#### Scenario: Modules con sort del cliente
- **WHEN** el cliente envía un sort (p. ej. por name o created_at)
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio
