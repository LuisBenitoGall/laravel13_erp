## Purpose

Catálogo global `Module`/`Functionality` con slug inmutable y provisión automática de permisos Spatie (R-AUT-07).

## Requirements

### Requirement: Catálogo Module y Functionality
El sistema SHALL mantener un catálogo global de `Module` (unidad SaaS-activable) y
`Functionality` (unidad de permiso y menú dentro de un módulo), con slug de
`Functionality` inmutable tras crearse (R-AUT-07).

#### Scenario: Alta de Functionality provisiona sus permisos
- **WHEN** se crea una `Functionality` nueva bajo un `Module`
- **THEN** se provisionan automáticamente sus 5 permisos
  (`viewAny`/`view`/`create`/`update`/`delete`) con prefijo igual al slug de la
  `Functionality`

#### Scenario: Slug de Functionality inmutable
- **WHEN** se edita una `Functionality` existente
- **THEN** solo se puede modificar `label`, `status` y `module_id` — el slug no es
  editable

#### Scenario: Module con campos de catálogo
- **WHEN** se crea o edita un `Module`
- **THEN** acepta `name`, `slug`, `label` (traducible), `color`, `icon`, `level`
  (`ModuleLevel`: AdminOnly/Basic/Optional), `status` (`ModuleStatus`: Active/Inactive) y
  `explanation` (traducible)

### Requirement: Status de Module aplicable también a shell
El sistema SHALL permitir cambiar el `status` (Active/Inactive) de cualquier `Module`
del catálogo, incluidos los slugs de shell `admin` y `workspace`. SHALL NOT rechazar
la desactivación solo por ser módulo de shell.

#### Scenario: Desactivar módulo admin
- **WHEN** un usuario autorizado actualiza el status del módulo `admin` a Inactive
- **THEN** el cambio se persiste y el módulo queda Inactive

#### Scenario: Desactivar módulo workspace
- **WHEN** un usuario autorizado actualiza el status del módulo `workspace` a Inactive
- **THEN** el cambio se persiste y el módulo queda Inactive

#### Scenario: Reactivar módulo shell
- **WHEN** un usuario autorizado actualiza el status de un módulo shell Inactive a
  Active
- **THEN** el cambio se persiste y el módulo queda Active

### Requirement: Alta de Module — slug y permiso
Al crear un `Module`, el cliente SHALL NOT enviar `slug`. El servidor SHALL generar el
slug desde `name`, garantizar unicidad case-insensitive e inmutabilidad tras el alta, y
provisionar el permiso comodín `{slug}.access`. Tras store exitoso SHALL redirigir a
Edit.

#### Scenario: Store sin slug en payload
- **WHEN** un usuario autorizado envía Create de Module sin campo slug y con name válido
- **THEN** el módulo se crea con slug derivado del name y permiso `{slug}.access`

#### Scenario: Slug único case-insensitive
- **WHEN** el slug derivado del name colisiona con un slug existente ignorando mayúsculas
- **THEN** la validación rechaza el alta

#### Scenario: Redirect a Edit tras alta
- **WHEN** el store de Module tiene éxito
- **THEN** la respuesta redirige a la ruta Edit de ese Module

### Requirement: Alta de Module — name y status
El alta de Module SHALL exigir `name` único case-insensitive, `level`, `icon`, `color` y
`status` (default Active si el cliente no fuerza otro valor válido).

#### Scenario: Name único case-insensitive
- **WHEN** se intenta crear un Module cuyo name colisiona con otro ignorando mayúsculas
- **THEN** la validación rechaza el alta

#### Scenario: Status por defecto Active
- **WHEN** se crea un Module sin indicar status inactivo
- **THEN** el módulo queda con status Active

### Requirement: Alta de Module — traducciones
`label` y `explanation` SHALL ser traducibles. Solo el locale principal del proyecto
(`config i18n.default`, hoy `es`) SHALL ser required; locales activos secundarios
opcionales.

#### Scenario: Traducciones — principal required
- **WHEN** falta label o explanation del locale principal
- **THEN** la validación rechaza el alta

#### Scenario: Traducciones — secundarios opcionales
- **WHEN** label y explanation del locale principal están presentes y otro locale activo
  queda vacío
- **THEN** el alta se acepta respecto a traducciones

### Requirement: UI Create de Module
La página Create de Module SHALL mostrar: nombre, nivel (select), icono (IconPicker
modal/grid), color (ColorPicker), status (toggle, default activo) y LangTabs con
label (text) + explanation (textarea simple, no Wysiwyg) por idioma activo. SHALL NOT
mostrar input de slug.

#### Scenario: Campos visibles Create
- **WHEN** el usuario abre `/admin/modules/create`
- **THEN** ve nombre, nivel, icono, color, status (toggle on) y LangTabs; no ve input
  de slug

#### Scenario: Explanation textarea
- **WHEN** edita explanation en una pestaña de LangTabs
- **THEN** el control es textarea simple (no Wysiwyg)

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
