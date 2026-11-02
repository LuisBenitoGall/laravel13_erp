# Spec Delta

## ADDED Requirements

### Requirement: Soft delete de Module en UI
El sistema SHALL permitir soft-delete de Module con confirmación desde Acciones del
Index y desde ActionBar del Edit. Tras delete desde Edit SHALL redirigir al Index.
SHALL NOT eliminar permisos Spatie. SHALL NOT soft-deletear por UI/API los módulos
shell `admin` y `workspace` (mantenimiento vía status Inactive).

#### Scenario: Delete desde Index
- **WHEN** el usuario confirma Eliminar en Acciones de un Module no shell
- **THEN** el Module se soft-deletea y deja de aparecer en el listado

#### Scenario: Delete desde Edit
- **WHEN** el usuario confirma Eliminar en ActionBar de Edit de un Module no shell
- **THEN** el Module se soft-deletea y la navegación termina en el Index de Módulos

#### Scenario: Shell no eliminable
- **WHEN** se intenta soft-deletear el Module `admin` o `workspace`
- **THEN** la operación se rechaza y el registro permanece

### Requirement: Retirar CRUD genérico de Functionalities
El sistema SHALL NOT exponer Index/Create/Edit ni table genéricos de Functionalities.
El flujo válido SHALL ser el CRUD anidado bajo Module Edit. SHALL eliminar
`ModuleForm.tsx` si no tiene callers.

#### Scenario: Sin rutas/páginas genéricas usables
- **WHEN** se revisa el área Admin Functionalities genérica
- **THEN** Index/Create/Edit/table globales están retirados; solo quedan endpoints
  nested/update/status/destroy usados por Module Edit

#### Scenario: ModuleForm eliminado si huérfano
- **WHEN** no quedan imports de `ModuleForm`
- **THEN** el fichero se elimina del repo

## MODIFIED Requirements

### Requirement: Edit de Module — campos e inmutables
En la pestaña Módulo, el usuario SHALL poder editar `name` (único case-insensitive,
excluyendo el propio registro), `icon` (IconPicker), `color` (ColorPicker), `label` y
`explanation` (LangTabs; locale principal required, secundarios opcionales) y `status`
(toggle). SHALL NOT mostrar ni aceptar `slug` en el update. SHALL NOT permitir editar
`level`, pero SHALL mostrar el level como información de solo lectura (etiqueta i18n).
Cambiar `name` SHALL NOT alterar el slug persistido.

#### Scenario: Name único en update
- **WHEN** se actualiza un Module con un name que ya usa otro Module (CI)
- **THEN** la validación rechaza el update

#### Scenario: Slug inmutable en update
- **WHEN** se actualiza el name de un Module
- **THEN** el slug en BD permanece igual al valor previo

#### Scenario: Level no editable en Edit
- **WHEN** se muestra Edit de Module
- **THEN** no hay control editable de level

#### Scenario: Level visible readonly
- **WHEN** el usuario abre Edit de Module en la pestaña Módulo
- **THEN** ve el nivel con etiqueta i18n y no puede cambiarlo

#### Scenario: Controles kit en Edit
- **WHEN** el usuario edita icono, color, traducciones o status
- **THEN** usa IconPicker, ColorPicker, LangTabs y toggle respectivamente
