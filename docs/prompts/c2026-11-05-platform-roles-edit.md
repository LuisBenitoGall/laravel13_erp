<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-05-platform-roles-edit
Rule: 10-architecture
Summary: Redesign del Edit de roles de plataforma (permisos incluidos) según
  layout y comportamiento de v1 `/admin/roles/{id}/edit`: ancho completo,
  organización de permisos como v1, persistencia ágil por checkbox sin
  recargar, Guardar solo para descripción, CTA copiar rol a otras companies.
Goals:
- Layout Edit roles plataforma **como v1** (ancho completo; anclas/organización
  de permisos igual que v1 — confirmado Luis).
- Persistencia de permisos al marcar/desmarcar checkbox (permiso individual o
  control global de toda la Functionality), **sin recargar** la vista; debe ser
  ágil.
- El botón Guardar de la vista (como en v1) **solo** persiste el campo editable
  de **descripción**, no los permisos.
- CTA copiar el rol editado a otra(s) empresa(s) del usuario en sesión (como
  v1), con reglas cerradas abajo.
- Semántica de permisos / comodín Module (respuesta Luis):
  - Si el rol recibe **al menos un** permiso ⇒ se sobreentiende Module y
    Functionality “activados” para ese rol (comodín `{module}.access` del
    Module correspondiente).
  - Un permiso habilita esa Functionality en el sentido de esa acción; **no**
    implica el resto de permisos de la misma Functionality ni otras
    Functionalities del mismo Module.
- Copy: si en la company destino ya existe un rol con el mismo `name`,
  **versionar** el nombre del rol recién creado.
- Fallo al persistir un toggle: **revertir** el checkbox + **toast**.
NonGoals:
- (No se listaron NonGoals explícitos en el debate más allá del alcance Edit
  plataforma / permisos / copy / descripción.)
Skills:
- openspec-propose
- v1-reference (pantalla Edit roles v1)
- tenancy-rbac (permisos / `{module}.access`)
Notes:
- Origen: Luis pidió Edit tras Create; referencia v1
  `/admin/roles/2/edit` (layout + copy + Guardar=descripción + checkboxes
  AJAX).
- Hallazgo explore: Edit v2 actual = `RoleForm` estrecho (`max-w-4xl`) con
  submit monolítico de matriz; v1 = form descripción + toggles AJAX + modal
  copy (solo no Super Admin y no universal en v1).
- Dudas explore cerradas por Luis (comodín/activación; versionado nombre en
  copy; layout v1 literal; revert+toast).
- Copy: explore planteó destino = companies del **perímetro** del usuario,
  rol local (`company_id` destino), solo no universales como v1 — Luis
  respondió «ok» al bloque copy + versionado; 10 no ampliar a universales ni
  a “todas las companies del sistema” sin preguntar.
- **Proceso**: explore se saltó el brief e invocó propose en el mismo hilo,
  dejando ya `openspec/changes/c2026-11-05-platform-roles-edit/`. Este fichero
  es el brief canónico. **10-architecture (sesión nueva)** debe
  revisar/reescribir esos artefactos contra este brief, no darlos por buenos.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Layout Edit roles plataforma estilo v1
El sistema SHALL presentar el Edit a ancho completo con la organización de
permisos equivalente a v1 (anclas/bloques Module y tarjetas Functionality).

#### Scenario: Vista ancho completo organizada como v1
- **WHEN** Super Admin abre Edit de un rol de plataforma gestionable
- **THEN** la vista sigue el layout v1 a ancho completo (no el contenedor
  estrecho actual)

### Requirement: Persistencia de permisos por checkbox sin recarga
El sistema SHALL persistir al cambiar un checkbox de permiso individual o el
control global de una Functionality, sin recargar. Un permiso SHALL NOT
conceder el resto de acciones de esa Functionality ni permisos de otras
Functionalities del mismo Module. Ante fallo SHALL revertir el checkbox y
mostrar toast.

#### Scenario: Toggle individual
- **WHEN** se marca o desmarca un permiso concreto
- **THEN** queda persistido sin recarga y no arrastra otras acciones ni otras
  funcs

#### Scenario: Toggle Functionality completa
- **WHEN** se usa el control global de una Functionality
- **THEN** se conceden o revocan los permisos de esa Functionality sin recarga

#### Scenario: Fallo de toggle
- **WHEN** falla la persistencia del checkbox
- **THEN** se revierte el estado visual y se muestra un toast de error

### Requirement: Activación Module al tener permisos
Cuando el rol obtiene al menos un permiso de Functionality de un Module, el
sistema SHALL dejar el Module “activado” para ese rol (comodín
`{module}.access`). Un permiso de Functionality no implica el resto de
permisos de esa Functionality ni otras Functionalities del Module.

#### Scenario: Al menos un permiso activa el Module
- **WHEN** el rol recibe el primer permiso bajo un Module
- **THEN** el rol tiene el comodín `{module}.access` de ese Module

### Requirement: Guardar solo descripción
El botón Guardar del Edit SHALL persistir la descripción editable. SHALL NOT
ser el mecanismo para guardar la matriz de permisos.

#### Scenario: Guardar descripción
- **WHEN** Super Admin cambia la descripción y pulsa Guardar
- **THEN** se actualiza la descripción; los permisos no dependen de ese submit

### Requirement: Copiar rol a companies del perímetro
El sistema SHALL ofrecer CTA Copiar (como v1: no aplicable a Super Admin ni a
universales) hacia companies del usuario en sesión. Si el `name` ya existe en
destino, SHALL versionar el nombre del rol nuevo.

#### Scenario: Copy con nombre libre
- **WHEN** se copia un rol no universal a una company del perímetro sin rol
  homónimo
- **THEN** existe el rol local con ese nombre y los permisos del origen

#### Scenario: Copy con colisión de nombre
- **WHEN** en destino ya existe un rol con el mismo `name`
- **THEN** el rol nuevo se crea con nombre versionado


## MODIFIED Requirements

### Requirement: Matriz de permisos en edición de rol de plataforma
La matriz deja de guardarse por submit monolítico: pasa a toggles inmediatos
y layout v1; el Guardar del formulario queda limitado a descripción (ver
requisitos ADDED).
