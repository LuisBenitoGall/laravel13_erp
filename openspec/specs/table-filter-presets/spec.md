## Purpose

Permite a cada usuario guardar, listar, renombrar y borrar hasta cinco
configuraciones nombradas de filtros avanzados por listado, aisladas por
usuario y company de sesión, sin aplicarlas automáticamente a la tabla.

## Requirements

### Requirement: Persistencia de presets por user company y tabla
El sistema SHALL persistir presets de filtros avanzados con alcance
usuario autenticado + company de sesión + identificador de listado
(`table_key`). Cada preset SHALL tener nombre y payload de filtros
compatible con el canal `filter` de ServerTable. SHALL NOT reutilizar la
entidad de favoritos de vista (`Preference`).

#### Scenario: Guardar preset
- **WHEN** un actor guarda la configuración actual del modal con un nombre
  válido y no supera el tope
- **THEN** el preset queda persistido para ese user+company+table_key

#### Scenario: Listar solo propios
- **WHEN** un actor lista presets para un `table_key`
- **THEN** solo ve los suyos de la company de sesión para ese listado

### Requirement: Tope de cinco presets por alcance
El sistema SHALL limitar a cinco presets por combinación
user+company+table_key. Un intento de crear por encima del tope SHALL
rechazarse con error claro (sin crear el registro).

#### Scenario: Rechazo al sexto
- **WHEN** ya existen cinco presets para esa tabla y el actor intenta
  guardar otro
- **THEN** la operación se rechaza y el conteo permanece en cinco

### Requirement: Seleccionar preset solo rellena el formulario
Al seleccionar un preset, el sistema SHALL rellenar los campos del modal
con su configuración y SHALL NOT aplicar esos filtros a la tabla hasta
que el actor pulse Aplicar.

#### Scenario: Cargar sin aplicar
- **WHEN** el actor selecciona un preset guardado
- **THEN** el formulario del modal refleja esa configuración y la tabla
  no cambia hasta Aplicar

### Requirement: Renombrar y borrar presets propios
El sistema SHALL permitir renombrar y borrar presets del propio usuario
en la company de sesión. Tras borrar, el preset SHALL dejar de listarse.

#### Scenario: Renombrar
- **WHEN** el actor renombra un preset propio con un nombre válido
- **THEN** el nuevo nombre queda persistido y aparece en el listado

#### Scenario: Borrar
- **WHEN** el actor borra un preset propio
- **THEN** deja de listarse para ese user+company+table_key

### Requirement: Aislamiento entre usuarios y companies
El sistema SHALL impedir listar, renombrar o borrar presets de otro
usuario o de otra company distinta a la de sesión.

#### Scenario: Otro usuario
- **WHEN** un actor intenta acceder a un preset de otro usuario
- **THEN** la operación se deniega

#### Scenario: Otra company
- **WHEN** un actor con sesión en company A intenta usar un preset
  guardado bajo company B
- **THEN** la operación se deniega
