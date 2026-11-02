# Spec Delta

## ADDED Requirements

### Requirement: ServerTable de Functionalities anidado
El listado de Functionalities bajo Module Edit SHALL usar el wrapper `ServerTable`
(R-FE-07). Columnas visibles: label, name, Acciones (toggle status, Edit, Delete con
confirm, en ese orden). SHALL NOT mostrar columna slug. SHALL filtrar por el módulo
padre. Toolbar/export: opt-in según necesidad del listado embebido (mínimo filtros de
cabecera e i18n del contrato ServerTable).

#### Scenario: Acciones Functionalities = toggle + Edit + Delete
- **WHEN** se muestra el ServerTable de Functionalities en Module Edit
- **THEN** Acciones ofrece toggle, Edit y Delete (confirm) en ese orden, sin columna
  slug

#### Scenario: Sin instancia Tabulator fuera de ServerTable
- **WHEN** se implementa el listado de Functionalities anidado
- **THEN** no se instancia Tabulator fuera de `ServerTable`
