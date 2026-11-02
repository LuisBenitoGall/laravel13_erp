<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-modules-edit
Rule: 10-architecture
Summary: Completar el Edit de Módulos con pestañas (info general + Funcionalidades como
    estructura), campos editables alineados al Create/kit, slug y level inmutables/ocultos,
    name único editable, quitar el menú genérico de Funcionalidades, y Volver siempre al
    Index de Módulos.
Goals:
- Proponer un **change nuevo** (no hay changes abiertos) para el **Edit** de Module.
- Vista Edit organizada en **tabs de página** al estilo v1: (1) Módulo / info general,
  (2) Funcionalidades — en este change solo la **estructura** de la pestaña
  Funcionalidades (sin CRUD; el CRUD anidado va en un change posterior).
- Tab Módulo: editar **name** (sigue siendo único), icono (IconPicker), color
  (ColorPicker), label + explanation (LangTabs; mismas reglas de idioma principal que
  Create), status (toggle). Reutilizar controles del kit/Create.
- **No** editar ni mostrar **slug** (inmutable aunque cambie el name; no regenerar desde
  el nombre). **No** editar **level** (fijado en el alta).
- Quitar del menú (y del flujo genérico) la entrada **Funcionalidades**: el CRUD de
  funcionalidades vivirá bajo Module/Edit, no en un Index genérico.
- Botón **Volver** en Modules/Edit: **siempre** regresa a Modules/Dashboard
  (`/admin/modules` / Index).
NonGoals:
- No implementar el CRUD de Functionalities en este change (solo dejar la pestaña /
  estructura de vista).
- No Create de Module (change aparte).
- No polish del Index salvo lo imprescindible para quitar el ítem de menú
  Funcionalidades.
- No Wysiwyg en explanation.
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (Module / menú / Functionality slug de menú)
- inertia-react-development (Edit Inertia + tabs)
Notes:
- Origen: `/opsx-explore`; referencia v1
  `http://laravel8_erp_viudavila.test/admin/modules/12/edit` (tabs Módulo |
  Funcionalidades). En v1 el update regeneraba slug desde name — en v2 **prohibido**.
- Estado actual v2: `Admin/Modules/Edit.tsx` → `ModuleForm` MVP plano; existe
  `/admin/functionalities` + seed/menú `functionalities` a retirar del menú/flujo
  genérico según Luis.
- Dependencia lógica: reutilizar LangTabs / IconPicker / ColorPicker / Switch del
  change Create (`c2026-11-02-admin-modules-create`) o del kit si ya está aplicado.
- Unicidad de `name` en update: única (excluyendo el propio registro); alinear con la
  regla case-insensitive del Create si ese contrato ya está cerrado.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Edit de Module con tabs de página
El sistema SHALL ofrecer la pantalla Edit de Module con dos pestañas de página:
«Módulo» (info general) y «Funcionalidades». En este change, la pestaña Funcionalidades
SHALL existir como estructura de vista (placeholder / sin CRUD). El CRUD de
funcionalidades anidado queda fuera de alcance.

#### Scenario: Tabs visibles en Edit
- **WHEN** el usuario abre Edit de un Module
- **THEN** ve las pestañas Módulo y Funcionalidades

#### Scenario: Pestaña Funcionalidades sin CRUD aún
- **WHEN** el usuario abre la pestaña Funcionalidades
- **THEN** la pestaña está presente en la UI pero no ofrece alta/edición/borrado de
  funcionalidades (diferido al change siguiente)

### Requirement: Campos editables e inmutables en Edit de Module
En la pestaña Módulo, el usuario SHALL poder editar: `name` (único; no colisionar con
otro Module), `icon`, `color`, `label` y `explanation` (vía LangTabs), y `status`
(toggle). SHALL NOT mostrar ni permitir editar `slug`. SHALL NOT permitir editar
`level`. Cambiar el nombre SHALL NOT regenerar ni alterar el slug. Controles de icono,
color, idiomas y status SHALL reutilizar el kit acordado en Create.

#### Scenario: Name editable y único
- **WHEN** se actualiza un Module con un name que ya usa otro Module
- **THEN** la validación rechaza el update

#### Scenario: Slug oculto e inmutable
- **WHEN** se muestra o se guarda Edit de Module
- **THEN** no hay campo slug en la UI y el slug persistido no cambia aunque cambie el
  name

#### Scenario: Level no editable
- **WHEN** se muestra Edit de Module
- **THEN** el level no es un control editable (no se puede cambiar en esta pantalla)

#### Scenario: Controles de presentación
- **WHEN** el usuario edita icono, color, label/explanation o status
- **THEN** usa IconPicker, ColorPicker, LangTabs y toggle de status respectivamente

### Requirement: Volver desde Edit a Index de Módulos
El botón Volver de Modules/Edit SHALL navegar siempre al Index/Dashboard de Módulos
(`/admin/modules`), no a otra pantalla.

#### Scenario: Volver a Modules Dashboard
- **WHEN** el usuario pulsa Volver en Modules/Edit
- **THEN** llega al Index de Módulos

### Requirement: Sin menú genérico de Funcionalidades
El sistema SHALL NOT exponer un ítem de menú de catálogo genérico «Funcionalidades»
(Index independiente). La gestión de funcionalidades queda anclada a Module/Edit (CRUD
en change posterior).

#### Scenario: Menú sin Funcionalidades genéricas
- **WHEN** un usuario con acceso al catálogo Admin ve el menú
- **THEN** no aparece la entrada de menú genérica de Funcionalidades (slug
  `functionalities` o equivalente de menú)
