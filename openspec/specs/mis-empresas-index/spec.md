## Purpose

Index Mis Empresas: listado ServerTable canónico de Companies del perímetro
del actor, con fecha de alta, toggle de status y acciones Edit / Delete /
Autorizaciones (SA).

## Requirements

### Requirement: Mis Empresas ServerTable canónico
El Index Mis Empresas SHALL listar Companies del perímetro del actor con
`ServerTable` conforme al contrato de referencia (ColumnDef, header
filters, persistKey, exportable, Acciones). SHALL incluir fecha de alta
(`companies.created_at`) con formato/alineación de columna date. SHALL NOT
mostrar columna `slug` ni columna Estado suelta (el estado va en toggle).

#### Scenario: Columnas y fecha
- **WHEN** el actor abre Mis Empresas
- **THEN** ve nombre, nombre comercial, nif y fecha de alta (created_at)
  maquetada como date; sin slug ni columna Estado

#### Scenario: Contrato ServerTable
- **WHEN** se revisa el Index
- **THEN** usa ColumnDef + filtros de cabecera + persistKey + exportable +
  columna Acciones

### Requirement: Acciones Mis Empresas
La columna Acciones SHALL ofrecer toggle de status (primero), Edit a la
ficha, acceso a Autorizaciones para Super Admin/operator, y Delete con
confirmación (último). SHALL NOT usar una columna de datos HTML suelta
para Autorizaciones.

#### Scenario: Toggle status
- **WHEN** el actor autorizado conmuta el status de una company según
  transiciones del enum (p. ej. draft→active, active→closed)
- **THEN** el estado se persiste y la fila refleja el cambio sin recarga
  completa

#### Scenario: Edit y Delete
- **WHEN** el actor autorizado usa Edit o Delete
- **THEN** Edit navega a la ficha de la company; Delete elimina con
  confirmación según Policy y perímetro de vínculo

#### Scenario: Autorizaciones en Acciones (SA)
- **WHEN** un Super Admin/operator ve Acciones de una fila
- **THEN** puede abrir Autorizaciones de esa company desde Acciones
