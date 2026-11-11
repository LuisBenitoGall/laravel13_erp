## Purpose

Teléfonos del User sobre tabla polimórfica `phones`: alta/edición/baja en
cards, primario único por owner, authz alineada a la ficha de usuario.

## Requirements

### Requirement: Schema polimórfico phones
El sistema SHALL persistir teléfonos en `phones` con `phoneable` polimórfico,
campos e164/ext/type/label/flags/notes, softDeletes y unique por
owner+e164. Para User, `country_id` SHALL ser null.

#### Scenario: Alta de teléfono de User
- **WHEN** un actor autorizado crea un teléfono en la ficha de un User
- **THEN** se persiste con phoneable ese User, `country_id` null y los
  campos del modal

#### Scenario: e164 duplicado en el mismo owner
- **WHEN** se crea o edita un teléfono con e164 ya existente en el mismo
  phoneable (activo)
- **THEN** la operación se rechaza con error de validación

### Requirement: Columna teléfonos en tab contactos
El tab Emails y teléfonos SHALL listar en la columna derecha los teléfonos
del User en cards, con alta y acciones tel, copy, WhatsApp si flag,
primario, editar y eliminar. El badge de verificación SHALL ser solo
informativo.

#### Scenario: Listado en cards
- **WHEN** el user tiene teléfonos
- **THEN** cada card muestra número, tipo, badges según flags y las
  acciones acordadas

#### Scenario: Sin verificación operativa
- **WHEN** se muestra una card
- **THEN** no hay flujo SMS/llamada de verificación

### Requirement: Alta y edición de teléfono por modal
El sistema SHALL permitir crear y editar vía modal: e164 obligatorio; ext,
tipo (`mobile`|`landline`|`fax`|`other`), etiqueta, notas; toggles WhatsApp
y Primario. Authz: self o `company-users.update` + perímetro.

#### Scenario: Alta válida
- **WHEN** un actor autorizado envía datos válidos no duplicados
- **THEN** se persiste el teléfono y aparece en el listado

#### Scenario: Edición válida
- **WHEN** un actor autorizado guarda cambios de un teléfono existente
- **THEN** el listado refleja los campos actualizados

#### Scenario: Ajeno sin permiso o fuera de perímetro
- **WHEN** un actor no autorizado fuerza store/update/destroy
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Primario único por owner
Al marcar un teléfono como primario (modal o acción estrella), el sistema
SHALL desmarcar cualquier otro primario del mismo phoneable en la misma
operación.

#### Scenario: Marcar primario
- **WHEN** se marca un teléfono como primario
- **THEN** ese queda primario y el resto del mismo owner dejan de serlo

### Requirement: Eliminar teléfono
El sistema SHALL permitir eliminar un teléfono con confirmación mediante
soft delete; el teléfono SHALL dejar de listarse.

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar
- **THEN** el teléfono desaparece del listado (soft-deleted)
