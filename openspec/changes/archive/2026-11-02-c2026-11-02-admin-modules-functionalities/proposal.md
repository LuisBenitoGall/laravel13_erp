# Proposal

## Why

La pestaña Funcionalidades del Edit de Module quedó como placeholder. Luis necesita el
CRUD anidado al estilo v1 (botón Nueva, tabla, toggle/Edit/Delete) con contrato v2
(slug auto, LangTabs, soft delete sin borrar permisos), siempre vía `ServerTable`.

## What Changes

- Sustituir el placeholder de la pestaña Funcionalidades por un `ServerTable` filtrado
  por `module_id` (columnas: label, name, Acciones; sin slug).
- ActionBar del Edit: botón **Nueva funcionalidad** → modal Create (name, LangTabs
  label, status default Active; slug server-side).
- Acciones de fila: toggle status → Edit (modal solo label) → Delete + confirm
  (soft delete; **no** borrar permisos Spatie).
- Alinear `StoreFunctionalityRequest` / store a slug auto + name unique CI (como
  Modules Create); Update limitado a label (+ status vía toggle endpoint).
- Endpoint nested table (p. ej. bajo `modules/{module}/functionalities/...`) y
  patch status JSON sin recarga.
- **BREAKING (contrato store Functionality):** el cliente ya no envía `slug`.
- No reabrir menú/Index genérico de Funcionalidades.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `admin-module-catalog`: CRUD Functionality anidado en Module Edit; dejar de exigir
  “pestaña solo estructura”; alta/edición/borrado/toggle según brief.
- `admin-ui-login`: escenario Edit con pestaña Funcionalidades operativa (tabla +
  ActionBar Nueva).
- `server-table-reference`: segunda aplicación de referencia — listado de
  Functionalities embebido (columnas/acciones acordadas).

## Impact

- Front: `Admin/Modules/Edit.tsx` (tabla + modales + ActionBar), Dialog + LangTabs.
- Backend: rutas nested o reuso de controller; `Store`/`Update` Functionality;
  `DeleteFunctionality` (ya soft-delete); toggle status; tests Feature.
- Specs: `admin-module-catalog`, `admin-ui-login`, `server-table-reference`.
- Fuera: menú genérico Functionalities; Wysiwyg; purge de permisos Spatie.
