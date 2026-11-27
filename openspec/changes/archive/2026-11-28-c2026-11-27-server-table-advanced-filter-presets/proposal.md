# Proposal

## Why

Los Index con ServerTable necesitan filtros avanzados reutilizables (más allá
de la cabecera) y que cada usuario pueda guardar hasta 5 configuraciones por
listado. El modal genérico ya existe pero no tiene disparador en toolbar ni
presets; Products aún no lo usa. Además se retira Family↔Category N:N del
backlog de Catalog (abandono explícito).

## What Changes

- Evolucionar kit: botón **Filtros avanzados** en toolbar ServerTable
  (izquierda de columnas); modal con campos por parámetro; Limpiar /
  Aplicar / Guardar.
- Entidad nueva de **presets** (≠ `Preference` favoritos): user + company +
  clave de tabla; máx. 5; CRUD nombre + JSON filtros; seleccionar solo
  rellena el form.
- Cablear **Products Index** como primer consumidor.
- Docs: `frontend.md`, ficha Catalog (quitar Family↔Category N:N).
- Tests Pest (tope 5, aislamiento, canal filter).

## Capabilities

### New Capabilities

- `table-filter-presets`: persistencia y API de presets de filtros
  avanzados por user/company/tabla.

### Modified Capabilities

- `server-table-reference`: disparador en toolbar + presets en el modal
  avanzado; contrato documentado del kit.
- `catalogue-products`: Index de Artículos monta el kit (campos +
  filterable taxonomía) listo para usar.

## Impact

- FE: `ServerTable`, `AdvancedSearchModal` (o wrapper); Products Index.
- BE: model/migration/Actions/routes presets (dominio Admin/shell).
- Products `ProductTableRequest` filterable si se amplían campos.
- Sin deps npm nuevas. E2E §4: **N/A**.
- Catalog backlog: sin Family↔Category N:N.
