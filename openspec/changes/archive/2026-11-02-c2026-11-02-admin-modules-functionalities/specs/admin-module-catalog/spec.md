# Spec Delta

## ADDED Requirements

### Requirement: Alta de Functionality anidada (modal)
Desde Module Edit, el alta de Functionality SHALL ocurrir en un modal. El cliente
SHALL NOT enviar `slug`: el servidor lo genera desde `name` (único case-insensitive,
inmutable). SHALL exigir `name` único CI, `label` (LangTabs; locale principal required),
`status` default Active, y `module_id` = módulo en edición. SHALL provisionar permisos
`{slug}.viewAny|view|create|update|delete` y `{module}.access` como el contrato v2
existente.

#### Scenario: Botón Nueva en ActionBar
- **WHEN** el usuario está en Modules/Edit
- **THEN** la ActionBar ofrece crear funcionalidad (abre modal)

#### Scenario: Store sin slug
- **WHEN** se guarda una Functionality válida desde el modal
- **THEN** se crea con slug auto, label, status Active por defecto, permisos
  provisionados y `module_id` del módulo actual

#### Scenario: Name único CI
- **WHEN** el name colisiona con otra Functionality ignorando mayúsculas
- **THEN** la validación rechaza el alta

### Requirement: Edit Functionality — solo label (modal)
El modal de edición SHALL permitir actualizar solo `label` (LangTabs; principal
required). SHALL NOT aceptar cambios de `name` ni `slug`.

#### Scenario: Update solo label
- **WHEN** se guarda el modal Edit de Functionality
- **THEN** solo cambia label; name y slug permanecen iguales

### Requirement: Toggle y delete de Functionality
El listado anidado SHALL permitir toggle de `status` sin recarga de página y Delete
con confirmación que hace soft delete. SHALL NOT eliminar permisos Spatie `{slug}.*`
al borrar.

#### Scenario: Toggle status JSON
- **WHEN** el usuario cambia el toggle de status de una Functionality
- **THEN** el status se persiste sin recargar la página y la fila se actualiza

#### Scenario: Soft delete sin borrar permisos
- **WHEN** el usuario confirma Delete
- **THEN** la Functionality queda soft-deleted y fuera del listado; los permisos
  Spatie asociados siguen existiendo

### Requirement: ServerTable Functionalities en Module Edit
La pestaña Funcionalidades SHALL mostrar un `ServerTable` con columnas: label, name y
Acciones (toggle → Edit → Delete); sin slug; filas solo del `module_id` actual.

#### Scenario: Columnas label name Acciones
- **WHEN** el usuario abre la pestaña Funcionalidades
- **THEN** ve ServerTable con etiqueta, nombre y Acciones (sin slug)

#### Scenario: Filtrado por módulo
- **WHEN** se solicitan datos del table de funcionalidades del módulo N
- **THEN** solo se devuelven Functionalities con `module_id` = N

## MODIFIED Requirements

### Requirement: Edit de Module — UI con tabs
La página Edit de Module SHALL mostrar dos pestañas de página: «Módulo» (info general)
y «Funcionalidades» (CRUD anidado vía ServerTable + modales; véanse requisitos de
Functionality anidada). El Volver de Edit SHALL ir siempre al Index de Módulos
(`/admin/modules`). La ActionBar SHALL incluir la acción de nueva funcionalidad.

#### Scenario: Tabs visibles en Edit
- **WHEN** el usuario abre Edit de un Module
- **THEN** ve las pestañas Módulo y Funcionalidades

#### Scenario: Pestaña Funcionalidades sin CRUD
- **WHEN** el usuario abre la pestaña Funcionalidades
- **THEN** ve el ServerTable de funcionalidades del módulo con acciones de toggle,
  Edit y Delete, y puede crear desde la ActionBar (CRUD anidado activo)

#### Scenario: Volver a Modules Index
- **WHEN** el usuario pulsa Volver en Modules/Edit
- **THEN** navega al Index de Módulos
