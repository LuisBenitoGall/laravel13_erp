## Why

Los listados deben seguir un patrón único (Tabulator vía `ServerTable`). El canon
`frontend.md` §3 ya lo describe, pero el wrapper actual es mínimo. Hay que **implementar
el contrato de referencia** (filtros de cabecera, Acciones, i18n, toolbar, export,
búsqueda avanzada opt-in), aplicarlo primero al dashboard de Módulos, y **actualizar la
norma** para que todo CRUD lo copie salvo excepción escrita.

## What Changes

- Ampliar `ServerTable`: header filters tipados; columna Acciones (`rowActions`); locale
  Tabulator reactivo; toolbar (columnas visibles + localStorage por usuario, page size,
  Excel/PDF).
- Componente modal búsqueda avanzada genérico (inputs por props); sin botón en Módulos.
- Modules Index como primera referencia (Acciones = solo Edit; exportable; sin advanced
  search).
- Alinear `ModuleTableRequest` / `TableBuilder` con filtros de columna + export.
- Actualizar `frontend.md` (+ templates frontend/backend de tabla si aplica).

## Capabilities

### New Capabilities

- `server-table-reference`: contrato ServerTable de referencia + Modules Index + norma.

### Modified Capabilities

- `admin-module-catalog` / `admin-ui-login`: listado Modules conforme al nuevo contrato
  (si el spec actual solo habla de ServerTable genérico, delta mínimo).
- `i18n-runtime` (opcional): solo si hace falta exigir reactividad locale→Tabulator.

## Impact

- `resources/js/Components/Table/ServerTable.tsx` (+ toolbar, formatters/actions helpers)
- `AdvancedSearchModal` (o nombre catálogo)
- `Admin/Modules/Index.tsx`, `ModuleController@table` (+ export), `ModuleTableRequest`
- `docs/standards/frontend.md`, `templates-frontend.md` / playbook si enlazan listados
- i18n claves tabla/paginador/tooltips/export
- Tests Feature table filters + smoke front

## Excepciones a standards

Ninguna. Migración masiva de otros Index: fuera de alcance (adoptarán después).
