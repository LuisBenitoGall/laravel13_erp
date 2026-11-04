# Design

## Context

See proposal.md — Why. v1: tabla `units` (name, slug, symbol, translations,
status int), softDeletes, Index DataTables + Create/Edit páginas,
`Unit::import()` desde `units.json` (~32 filas, IDs, ~30 Active). v2: patrón
Currencies; Configuración vía `CONFIGURATION_SLUGS`. Brief: dominio Admin,
omitir translations, status del JSON, modales, Active-first.

## Goals / Non-Goals

**Goals:**

- `Unit` global en Admin + CRUD Configuración / Unidades (modales).
- Functionality `units` + menú; seeder con IDs/status; Active-first + name.

**Non-Goals:**

- `translations`; ProductUnits; import en Index; jsonUnits(); E2E; módulo SaaS
  nuevo; dominio Catalog/Units aparte.

## Decisions

### D1 — Dominio Admin (Configuración)
**Elección:** `app/Domains/Admin` como Currency/Country. Functionality bajo
módulo `admin`. En module-map: añadir `Unit` a entidades Admin; en Catalog dejar
claro que “unidades” de producto **leen** el catálogo Admin (FK futura), no
poseen el maestro.
**Descartado:** dominio Catalog o Units (Luis: functionalities de Configuración).

### D2 — Sin translations
**Elección:** No columna. Name/symbol bastan; i18n de UI vía claves Laravel.
**Descartado:** JSON nullable sin UI.

### D3 — Status string + seed del JSON
**Elección:** Enum Active/Inactive; seeder map 1→active, 0→inactive; IDs del
`units.json` / dump. `UnitsSeeder` fuera de DatabaseSeeder; README.
**Descartado:** forzar todo Active.

### D4 — symbol max 4
**Elección:** Como v1 validator. name unique entre no trashed.

### D5 — UI + Active-first
**Elección:** Rutas `admin/units*`; Index ServerTable (name, symbol); Acciones
toggle→Edit→Delete; modales Create/Edit; sin columna Estado; sorts vacío →
Active-first + name (helper existente o CASE local).
**Descartado:** páginas full-page v1.

### D6 — Menú / permisos
Functionality `units` + `units.*`; CONFIGURATION_SLUGS → Index units.

### D7 — Delete
Soft delete. Sin consumidores aún.

### D8 — Vinculaciones / E2E
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | `units` + `units.*` |
| V-08 | Sí | Configuración → Unidades |
| V-09 | No | Módulo `admin` |
| R-TEN-05 | N/A | Global; test cross-company |
| E2E | N/A | Feature tests |

## Risks / Trade-offs

- [Catalog vs Admin en mapa] → Nota explícita: maestro en Admin; Catalog solo
  consume.
- [IDs preservados] → BD limpia; no re-seed con FKs producto futuras.

## Migration Plan

1. Migración → Functionality → seeder → menú + README + glosario/ficha/mapa.
2. Rollback: menú/Functionality off; dropear tabla si no hay FKs.

## Open Questions

Ninguna.
