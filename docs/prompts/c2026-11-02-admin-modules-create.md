<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-modules-create
Rule: 10-architecture
Summary: Completar el Create de Módulos (solo alta) alineado al formulario v1, con
    LangTabs reutilizable del kit (label + explanation por idioma activo), icono vía
    modal/grid curado, colorpicker, status toggle (default activo), slug autoinmutable
    y redirect a Edit tras guardar.
Goals:
- Proponer un **change nuevo** (no hay changes abiertos) que cubra únicamente la
  **creación** de Module.
- Introducir el componente reutilizable de tabs de idioma del form-kit (`LangTabs` /
  equivalente normativo) como sistema general del proyecto, con el Create de Módulos
  como primer consumidor.
- Formulario Create con campos: nombre, nivel (select), icono (modal + grid), color
  (colorpicker), status (toggle, por defecto activo), y por cada idioma activo en tabs:
  label (text) + explanation (textarea simple, no Wysiwyg).
- Todos los campos del formulario son obligatorios salvo las traducciones de locales
  no principales (solo el idioma principal es required para label/explanation).
- Nombre único (case-insensitive); slug generado automáticamente desde el nombre,
  único (case-insensitive), no editable en Create ni visible como campo de entrada;
  el slug alimenta el permiso comodín `{slug}.access` (semántica v2 ya existente).
- Tras Guardar con éxito: redirigir a **Edit** del módulo creado.
- Iconos del modal: grid con un catálogo curado de iconos Lucide útiles en un ERP
  (sin emoticonos / iconos frívolos).
NonGoals:
- No implementar Edit ni Delete de Módulos en este change (vendrán después).
- No pulir el Index de Módulos en este change.
- No Wysiwyg para explanation (textarea simple).
- No inventar flujos de “módulo en pruebas” más allá del status Active/Inactive del
  catálogo.
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (Module catálogo / `{slug}.access`)
- inertia-react-development (Create Inertia + React)
- laravel-best-practices (FormRequest / Action CreateModule)
Notes:
- Origen: `/opsx-explore`; referencia UI v1
  `http://laravel8_erp_viudavila.test/admin/modules/create` + capturas LangTabs
  (label+textarea por idioma). En v1 el icono era texto libre; en v2 Luis pide modal
  grid Lucide.
- Estado actual v2: `ModuleForm` MVP con slug editable, label es/en sueltos, status
  select, color/icon inputs texto — hay que rehacer Create según este contrato.
  Backend ya tiene `CreateModule` + `StoreModuleRequest` + permiso `{slug}.access`.
- LangTabs: pendiente en form-kit (`frontend.md` / spec `form-kit`); este change lo
  añade al kit y lo usa en Create. Tabs dinámicos según idiomas activos del runtime;
  patrón mental v1 `name=<campo>_<codigo2letras>` → en v2 payload Spatie/JSON por
  locale (decisión de wire en design de 10).
- Idioma principal obligatorio: el locale principal del proyecto (hoy `es` en el
  runtime i18n); resto de locales activos opcionales.
- Status: control tipo toggle; default **activo** (`true` / Active).
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: LangTabs reutilizable en el form-kit
El sistema SHALL exponer un componente reutilizable de pestañas de idioma (LangTabs)
en el kit de formularios. Las pestañas SHALL generarse dinámicamente a partir de los
idiomas activos del runtime. Cada pestaña SHALL poder alojar los campos traducibles
que declare el formulario consumidor.

#### Scenario: Tabs según idiomas activos
- **WHEN** un formulario usa LangTabs y el runtime expone N idiomas activos
- **THEN** se renderizan N pestañas, una por idioma, con la etiqueta de idioma
  correspondiente

#### Scenario: Contenido por pestaña
- **WHEN** el consumidor declara campos dentro de LangTabs
- **THEN** al cambiar de pestaña se muestran los inputs de ese idioma (mismo layout,
  valores por locale)

### Requirement: Create de Module (alta)
El sistema SHALL ofrecer un formulario de creación de Module con: nombre (único,
case-insensitive), nivel (select obligatorio), icono (obligatorio; selección vía modal
con grid de iconos Lucide curados para ERP, sin emoticonos), color (obligatorio;
colorpicker), status (toggle obligatorio; valor por defecto activo). SHALL NOT mostrar
campo de entrada de slug: el slug se genera en servidor desde el nombre, es único
case-insensitive e inmutable tras crearse. El alta SHALL crear el permiso comodín
`{slug}.access` (comportamiento v2 existente). Tras guardar con éxito SHALL redirigir
a la pantalla Edit del módulo creado.

#### Scenario: Campos visibles en Create
- **WHEN** el usuario abre Create de Module
- **THEN** ve nombre, nivel, icono (selector modal), color (colorpicker), status
  (toggle default activo) y LangTabs; no ve input de slug

#### Scenario: Nombre y slug únicos case-insensitive
- **WHEN** se intenta crear un Module con nombre o slug derivado que colisiona
  ignorando mayúsculas/minúsculas con uno existente
- **THEN** la validación rechaza el alta

#### Scenario: Slug auto desde nombre
- **WHEN** se crea un Module con un nombre válido
- **THEN** el slug se deriva del nombre en servidor y queda persistido sin haber sido
  editable en el formulario Create

#### Scenario: Icono desde modal grid
- **WHEN** el usuario abre el selector de icono y elige uno del grid
- **THEN** el valor del icono del formulario es el nombre Lucide seleccionado

#### Scenario: Redirect a Edit
- **WHEN** el alta de Module se completa con éxito
- **THEN** el usuario es redirigido a Edit de ese Module

### Requirement: Traducciones label y explanation en Create
En Create de Module, LangTabs SHALL incluir por cada idioma activo: `label` (input
text) y `explanation` (textarea simple). Solo el idioma principal del proyecto SHALL
ser obligatorio para esos campos; el resto de locales activos MAY quedar vacíos.

#### Scenario: Idioma principal required
- **WHEN** se envía Create sin label (o sin explanation) del idioma principal
- **THEN** la validación falla

#### Scenario: Locales secundarios opcionales
- **WHEN** se envía Create con label y explanation del idioma principal y sin
  rellenar otro idioma activo
- **THEN** el alta se acepta (respecto a traducciones)

#### Scenario: Explanation sin Wysiwyg
- **WHEN** se edita explanation en una pestaña de idioma
- **THEN** el control es un textarea simple (no editor enriquecido)
