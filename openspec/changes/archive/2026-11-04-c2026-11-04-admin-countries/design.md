# Design

## Context

See proposal.md — Why. v1: tablas `countries`/`provinces`/`towns`, softDeletes,
status int, Controllers Admin + DataTables + modales, cascada AJAX
(`getCountryProvinces` / `getProvinceTowns` filtrando status=1). v2: catálogos
globales vía playbook `--global`; Configuración SA vía `MenuBuilder::CONFIGURATION_SLUGS`;
Modules como referencia de ServerTable + Acciones + status string.

## Goals / Non-Goals

**Goals:**

- Tres entidades globales en `app/Domains/Admin` con CRUD drill v1→v2.
- Functionality `countries` + menú; cascada JSON; seeder datos v1 con IDs estables.
- Enum status compartido; Policies por modelo o Policy Country que autorice hijos vía
  `countries.*` (una Functionality).

**Non-Goals:**

- Upload flag; consumidores Partners; módulo SaaS nuevo; E2E browser.

## Decisions

### D1 — Preservar IDs del seeder v1
**Elección:** El seeder inserta con los **mismos IDs** que v1 (countries, provinces,
towns). Facilita FKs futuras (workplaces, obras, companies.country_id) sin mapa de
traducción.
**Alternativa descartada:** IDs autoincrement nuevos — rompe continuidad de datos
maestros entre v1 y v2.
**Impl:** `Country::unguarded` / upsert por id, o inserts explícitos; tras seed,
ajustar autoincrement MySQL si hace falta.

### D2 — Status string + enum (opción C del brief)
**Elección:** Enum PHP `GeographyStatus` (o reutilizar patrón Active/Inactive como
`ModuleStatus`) en columna `string`; mapear seeder 1→`active`, 0→`inactive`. Soft
Deletes en las tres tablas.
**Descartado:** mantener integer 0/1 (rompe R-ESTADOS / patrón Modules).

### D3 — Una Functionality `countries`, tres Policies
**Elección:** Policies `CountryPolicy` / `ProvincePolicy` / `TownPolicy` (o una
trait) que delegan todas en Spatie `countries.viewAny|view|create|update|delete`.
CreateFunctionality en seeder AdminCatalog (o CreateFunctionality::handle) si no
existe.
**Descartado:** Functionalities `provinces`/`towns` separadas — v1 no las tiene.

### D4 — UI drill + modales (no tabs Module-style)
**Elección:**
- Rutas: `admin/countries`, `admin/countries/{country}/provinces`,
  `admin/provinces/{province}/towns` (o equivalente claro anidado).
- Páginas Index ServerTable por nivel; Acciones: toggle status → drill hijo (solo
  Country/Province) → Edit (modal) → Delete confirm.
- Create/Edit en Dialog + Form kit v2 (espejo campos v1).
**Descartado:** tabs Functionalities dentro de Edit de país.

### D5 — Cascada
**Elección:** GET JSON autenticados, p. ej. `admin/countries/{country}/provinces/options`
y `admin/provinces/{province}/towns/options`, auth + `countries.viewAny` (o auth
mínima de sesión ERP). Solo Active + not trashed. Payload mínimo `{id, name}`
ordenado por name.

### D6 — Seeder datos
**Elección:** Extraer/portar desde seeders v1 (`Countries` si existe,
`ProvincesTableSeeder`, `TownsTableSeeder` / Viudavila). Fichero(s) en
`database/seeders/` invocables desde DatabaseSeeder o comando documentado.
Idempotente: `updateOrInsert` por `id`. Volumen towns: aceptable en seeder PHP o
JSON chunk; 20 elige formato sin cambiar contrato de IDs.

### D7 — Delete
**Elección:** Soft delete. Si hay hijos no trashed, 20 decide A bloquear / B soft
cascade — **A (bloquear)** por defecto (no huérfanos silenciosos); documentar en
FormRequest/Action.

### D8 — E2E
N/A (`e2e.md` §4).

## Risks / Trade-offs

- [Seeder towns enorme] → Mitigación: generar desde v1 en apply; CI puede seed
  subset en tests + smoke “count > 0” / España presente.
- [IDs preservados vs tablas ya parcialmente migradas] → Solo aplica en BD limpia;
  documentar “no re-seed si hay FKs consumidoras”.
- [Configuración solo SA hoy] → Países en CONFIGURATION_SLUGS como Modules; acceso
  también vía `countries.*` + módulo admin activo (misma frontera que otros ítems).

## Migration Plan

1. Migraciones tres tablas → seed Functionality → seed geografía → menú.
2. Rollback: quitar menú/Functionality; dropear tablas si no hay FKs externas.

## Open Questions

Ninguna bloqueante.
