# Spec Delta

## ADDED Requirements

### Requirement: Edit de Module — UI con tabs
La página Edit de Module SHALL mostrar dos pestañas de página: «Módulo» (info general)
y «Funcionalidades». En este change, Funcionalidades SHALL ser solo estructura (sin
CRUD). El Volver de Edit SHALL ir siempre al Index de Módulos (`/admin/modules`).

#### Scenario: Tabs visibles en Edit
- **WHEN** el usuario abre Edit de un Module
- **THEN** ve las pestañas Módulo y Funcionalidades

#### Scenario: Pestaña Funcionalidades sin CRUD
- **WHEN** el usuario abre la pestaña Funcionalidades
- **THEN** la pestaña existe pero no ofrece alta/edición/borrado de funcionalidades

#### Scenario: Volver a Modules Index
- **WHEN** el usuario pulsa Volver en Modules/Edit
- **THEN** navega al Index de Módulos

### Requirement: Edit de Module — campos e inmutables
En la pestaña Módulo, el usuario SHALL poder editar `name` (único case-insensitive,
excluyendo el propio registro), `icon` (IconPicker), `color` (ColorPicker), `label` y
`explanation` (LangTabs; locale principal required, secundarios opcionales) y `status`
(toggle). SHALL NOT mostrar ni aceptar `slug` ni `level` en el update. Cambiar `name`
SHALL NOT alterar el slug persistido.

#### Scenario: Name único en update
- **WHEN** se actualiza un Module con un name que ya usa otro Module (CI)
- **THEN** la validación rechaza el update

#### Scenario: Slug inmutable en update
- **WHEN** se actualiza el name de un Module
- **THEN** el slug en BD permanece igual al valor previo

#### Scenario: Level no editable en Edit
- **WHEN** se muestra Edit de Module
- **THEN** no hay control editable de level

#### Scenario: Controles kit en Edit
- **WHEN** el usuario edita icono, color, traducciones o status
- **THEN** usa IconPicker, ColorPicker, LangTabs y toggle respectivamente
