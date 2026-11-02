<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-modules-functionalities
Rule: 10-architecture
Summary: Completar la pestaña Funcionalidades del Edit de Module al estilo v1: botón
    Nueva en ActionBar, ServerTable filtrado por módulo, modales Create/Edit, acciones
    toggle + Edit + Delete; alta con contrato v2 (slug auto, LangTabs label); edit solo
    label; soft delete sin borrar permisos Spatie.
Goals:
- Proponer un **change nuevo** (no hay changes abiertos) para el CRUD de Functionalities
  **anidado** bajo Module/Edit (pestaña Funcionalidades).
- Botón **Nueva funcionalidad** en la **ActionBar** del Edit del módulo (visible como
  en v1).
- En la pestaña Funcionalidades: `<ServerTable>` (Tabulator) con columnas como v1:
  etiqueta (`label`) + nombre (`name`) + Acciones; filtrado al `module_id` del módulo
  en edición; sin columna slug.
- Acciones en este orden: **toggle** status → **Edit** → **Delete** (con confirm).
- Create (modal): contrato **v2** — `name` único, slug auto desde name (no visible /
  no editable), `label` con LangTabs (locale principal required, secundarios opcionales),
  `status` default activo; provisionar permisos `{slug}.*` como ya hace
  `CreateFunctionality`; `module_id` = módulo actual.
- Edit (modal): **solo** `label` (LangTabs); no name, no slug, no level.
- Delete: soft delete del Functionality; **SHALL NOT** eliminar los permisos Spatie
  asociados.
- UX Create/Edit: **modales** (Dialog), no páginas hijas independientes.
NonGoals:
- No reabrir Index/menú genérico de Funcionalidades.
- No cambiar el tab Módulo (info general) salvo lo mínimo para ActionBar / tabs.
- No Wysiwyg; no borrar permisos al soft-delete.
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (Functionality / permisos / menú)
- inertia-react-development (Edit + ServerTable + Dialog)
Notes:
- Origen: `/opsx-explore`; referencia v1
  `admin/modules/{id}/edit` (modal nueva: nombre+etiqueta; modal edit: solo etiqueta;
  tabla etiqueta|nombre|toggle+edit+delete). Edit Module change previo ya dejó la
  pestaña como estructura.
- Backend v2 existente: `CreateFunctionality`, `UpdateFunctionality` (slug inmutable),
  `FunctionalityController` genérico, `StoreFunctionalityRequest` aún pide slug del
  cliente — alinear a slug server-side como Modules Create.
- Endpoint tabla anidada: algo tipo `/admin/modules/{module}/functionalities/table`
  (decisión de wire en design de 10).
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: ServerTable de Functionalities en Module Edit
En la pestaña Funcionalidades del Edit de Module, el sistema SHALL mostrar un
`ServerTable` con columnas visibles: label (etiqueta), name (nombre) y Acciones; sin
slug. Los datos SHALL estar filtrados al módulo en edición.

#### Scenario: Columnas como v1
- **WHEN** el usuario abre la pestaña Funcionalidades de un Module
- **THEN** ve ServerTable con etiqueta, nombre y Acciones (sin slug)

#### Scenario: Solo del módulo actual
- **WHEN** se cargan filas del ServerTable de funcionalidades
- **THEN** solo aparecen Functionalities con ese `module_id`

### Requirement: Acciones de fila Functionalities
La columna Acciones SHALL ofrecer, en este orden: toggle de status, Edit (abre modal) y
Delete con confirmación.

#### Scenario: Orden de acciones
- **WHEN** se renderiza Acciones de una funcionalidad
- **THEN** aparecen toggle, Edit y Delete en ese orden

#### Scenario: Toggle status sin recarga de página
- **WHEN** el usuario cambia el toggle de status
- **THEN** el status se persiste sin recargar la página y la fila refleja el nuevo
  estado

#### Scenario: Delete con confirm y soft delete
- **WHEN** el usuario confirma Delete de una Functionality
- **THEN** el registro se soft-deletea y desaparece del listado; los permisos Spatie
  `{slug}.*` permanecen

### Requirement: Alta de Functionality vía modal
El Edit de Module SHALL exponer en ActionBar un botón para nueva funcionalidad que
abre un modal. El alta SHALL exigir name único, generar slug en servidor (único,
inmutable), label vía LangTabs (principal required), status default Active, y
asociar `module_id` al módulo actual. SHALL provisionar permisos de la Functionality
como el contrato v2 existente. SHALL NOT mostrar input de slug.

#### Scenario: Botón en ActionBar
- **WHEN** el usuario está en Modules/Edit
- **THEN** la ActionBar ofrece la acción de crear funcionalidad

#### Scenario: Create en modal con contrato v2
- **WHEN** guarda una funcionalidad nueva válida desde el modal
- **THEN** se crea con slug auto, label traducido, status Active por defecto, permisos
  provisionados, y aparece en el ServerTable del módulo

### Requirement: Edit de Functionality — solo label
El modal de edición de Functionality SHALL permitir cambiar solo `label` (LangTabs).
SHALL NOT permitir editar name ni slug.

#### Scenario: Edit solo label
- **WHEN** el usuario abre Edit de una Functionality y guarda
- **THEN** solo se actualiza label; name y slug permanecen iguales
