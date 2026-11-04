# Proposal

## Why

El ERP necesita el maestro geográfico (país → provincia → población) que ya existe
en v1, bajo Configuración, como catálogo global. Sin él no hay base para direcciones
de partners/obras. Se porta la lógica y los datos de v1 al stack v2 (Inertia,
ServerTable, Policies, sin Livewire).

## What Changes

- Entidades globales `Country`, `Province`, `Town` (sin `company_id`), soft delete,
  status string Active/Inactive + enum PHP.
- CRUD Admin anidado al estilo v1: Index países → provincias → poblaciones; modales
  Create/Edit; Acciones (toggle, drill, edit, delete).
- Functionality `countries` bajo módulo `admin`; permisos `countries.*` para las tres
  entidades; menú Configuración → Países.
- Endpoints JSON de cascada país→provincias y provincia→poblaciones.
- Seeder con datos v1 (status 1/0 → active/inactive); **IDs preservados**.
- Glosario + ficha Admin actualizados.
- Campo `flag` nullable sin upload.

## Capabilities

### New Capabilities
- `admin-geography-catalog`: catálogo global Country/Province/Town, CRUD Configuración,
  cascada JSON y seeder v1.

### Modified Capabilities
- `menu-core-pillars`: Configuración SA incluye Países hacia el Index de countries.

## Impact

- Dominio Admin: models, migrations, Actions, Policies, FormRequests, controllers,
  Resources, routes, páginas Inertia `Admin/Countries|Provinces|Towns`, i18n,
  `MenuBuilder` + seeder Functionality, seeder datos geográficos (volumen towns).
- Tests feature (authz, jerarquía, cascada, seed smoke, visibilidad cross-company).
- E2E: N/A (`e2e.md` §4).
- Sin module-map nuevo (Admin ya existe); sin dependencias Composer/npm nuevas.
