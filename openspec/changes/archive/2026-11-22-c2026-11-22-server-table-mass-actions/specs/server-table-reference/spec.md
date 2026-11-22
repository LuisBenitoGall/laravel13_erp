# Spec Delta

## ADDED Requirements

### Requirement: Acciones masivas opt-in en ServerTable
El sistema SHALL permitir que `<ServerTable>` declare opt-in una lista dinámica
de acciones masivas. Con lista no vacía SHALL mostrar checkbox como primera
columna (no ocultable) y un desplegable a la izquierda de «Mostrar/ocultar
columnas». Sin lista SHALL NOT mostrar columna ni desplegable.

#### Scenario: Tabla sin mass actions
- **WHEN** un Index usa ServerTable sin declarar acciones masivas
- **THEN** no aparece columna de checkbox ni desplegable de acciones masivas

#### Scenario: Tabla con mass actions
- **WHEN** un Index declara una lista no vacía de acciones masivas
- **THEN** aparece la columna checkbox a la izquierda y el desplegable a la
  izquierda de «Mostrar/ocultar columnas» con esas acciones

### Requirement: Selección de página y dropdown deshabilitado
La selección de acciones masivas SHALL limitarse a la página actual y SHALL
NOT retenerse al cambiar de página. El desplegable SHALL permanecer
deshabilitado mientras no haya al menos una fila marcada. Acciones con
confirm SHALL usar el mismo patrón de confirmación que las acciones de fila.

#### Scenario: Desplegable sin selección
- **WHEN** hay acciones masivas pero ninguna fila marcada
- **THEN** el desplegable está deshabilitado

#### Scenario: Selección solo página actual
- **WHEN** el usuario marca checkboxes y cambia de página
- **THEN** la selección no conserva filas de la página anterior

### Requirement: Norma frontend mass actions
`docs/standards/frontend.md` SHALL documentar el contrato mass actions opt-in
y que, al pedirlas en un Index, el agente las incluya y pregunte qué acciones
lleva.

#### Scenario: Norma publicada
- **WHEN** un agente implemente o extienda un Index tras este change
- **THEN** `frontend.md` describe mass actions opt-in y la regla de preguntar
  el set de acciones al activarlas en una tabla
