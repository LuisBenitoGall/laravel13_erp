# Spec Delta

## Purpose

Listado ServerTable de centros vinculados a una Delegación, con sede
preferida por Delegación (≤1, opcional) editable desde su ficha Edit.

## ADDED Requirements

### Requirement: ServerTable de sedes vinculadas
El tab Centros de trabajo del Edit Delegación SHALL listar los workplaces
de la matriz vinculados a esa Delegación mediante `<ServerTable>`
server-side. Columnas SHALL incluir nombre, código postal, población,
provincia, país, fecha de creación y sede preferida de la Delegación.
SHALL NOT mostrar la preferida-por-sede del mapa (`is_preferred`).

#### Scenario: Columnas visibles
- **WHEN** un actor autorizado abre Edit Delegación → Centros de trabajo con
  sedes vinculadas
- **THEN** ve ServerTable con CP, población, provincia, país, creación y
  sede preferida de la Delegación

#### Scenario: Sin lista HTML
- **WHEN** hay sedes vinculadas
- **THEN** no se usa el listado HTML de solo nombre + badge de preferida-por-sede

### Requirement: Sede preferida por Delegación
El sistema SHALL permitir como máximo una sede marcada como preferida de la
Delegación entre sus workplaces vinculados. SHALL NOT exigir que exista.
SHALL permitir marcarla o quitarla desde el Edit Delegación. SHALL NOT
alterar las marcas de preferida-por-sede (`is_preferred`).

#### Scenario: Como máximo una preferida
- **WHEN** el actor marca la sede W2 como preferida de la Delegación D que
  ya tenía W1 preferida
- **THEN** solo W2 queda como sede preferida de D

#### Scenario: Sin preferida
- **WHEN** una Delegación tiene vínculos pero ninguna sede preferida
- **THEN** el estado es válido

#### Scenario: Preferida por sede intacta
- **WHEN** se cambia la sede preferida de la Delegación
- **THEN** las marcas `is_preferred` de cada Workplace no se reinterpretan
  ni se borran por ese cambio

### Requirement: Perímetro del table y de la preferida
Las lecturas del table y el set de sede preferida SHALL respetar el perímetro
de Delegaciones (hija de la matriz de sesión) y que el workplace esté
vinculado a esa Delegación.

#### Scenario: Workplace no vinculado
- **WHEN** se intenta marcar como preferida una sede no vinculada a D
- **THEN** se rechaza la operación
