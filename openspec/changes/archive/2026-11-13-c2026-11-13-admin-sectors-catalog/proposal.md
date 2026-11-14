# Proposal

## Why

Antes de marcas hace falta un catálogo de **sectores** en Configuración:
globales (sin `company_id`), traducibles, con CRUD. Cada Company podrá
atribuirse N sectores a su criterio (1→N).

## What Changes

- Entidad global `Sector` (translations; soft delete; status).
- CRUD Configuración → Sectores (ServerTable + modales; Functionality
  `sectors` / `sectors.*`).
- Pivot Company↔Sector (N:N); la empresa elige sus sectores.
- Menú / RealignMenuCatalog / glosario / ficha Admin.
- NonGoal: Brand; no reutilizar el `company_sectors` v1 (allí era
  company↔category).

## Capabilities

### New Capabilities

- `admin-sectors-catalog`: catálogo global Sector + CRUD Configuración +
  traducciones + vínculo N:N Company↔Sector.

### Modified Capabilities

- `company-edit`: permitir atribuir sectores (N) a la company en Edit
  (sin mezclar con Brand).

## Impact

- Migraciones `sectors` + pivot; modelo/Policy/Actions; rutas `/admin/sectors`.
- UI Index + modales LangTabs; Edit Company selector multi.
- Seeder/menu Functionality bajo `configuracion`.
- Tests Feature catálogo + authz + sync company.
