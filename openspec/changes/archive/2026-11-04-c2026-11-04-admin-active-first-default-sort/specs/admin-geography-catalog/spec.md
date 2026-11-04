# Spec Delta

## ADDED Requirements

### Requirement: Default Active-first en Indexes geográficos
Los endpoints `table` de Countries, Provinces y Towns SHALL, cuando el cliente
no envía sorts, ordenar Active antes que Inactive y, como criterio secundario,
por `name`. SHALL NOT prefijar Active-first cuando el cliente envía sorts.

#### Scenario: Países sin sort del cliente
- **WHEN** se solicita el `table` de countries sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo de status, por name

#### Scenario: Provincias y poblaciones sin sort
- **WHEN** se solicita el `table` de provinces o towns sin sorts
- **THEN** Active primero y luego name

#### Scenario: Geografía con sort del cliente
- **WHEN** el cliente envía un sort en countries, provinces o towns
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio
