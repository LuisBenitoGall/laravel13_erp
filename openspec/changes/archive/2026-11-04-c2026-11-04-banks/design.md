# Design

## Context

See proposal.md — Why. v1: tabla `banks` (+ swift, swift_source, swift_updated_at),
softDeletes, status bool/int, Index DataTables + Create/Edit páginas,
`Bank::import()` al Index desde `banks.enriched.json` (~244 filas, ~17 Active,
IDs en JSON). v2: patrón Currencies (modales, ServerTable, seeder Artisan,
Active-first); Configuración vía `MenuBuilder::CONFIGURATION_SLUGS`. Brief: dominio
código **Banks**, no Admin/Treasury.

## Goals / Non-Goals

**Goals:**

- Dominio `Banks` con `Bank` global + CRUD Configuración / Bancos (modales).
- Functionality `banks` + menú; seeder enriched con IDs/status del JSON; README.
- Active-first + name si sorts vacío; module-map actualizado.

**Non-Goals:**

- BankAccount; BanksSyncSwift; jsonBanks(); import en Index; E2E; módulo SaaS nuevo.

## Decisions

### D1 — Dominio Banks (capa maestros)
**Elección:** `app/Domains/Banks`. En module-map: dominio **Banks** en Capa 1
(maestros / catálogos globales), entidad `Bank`. **Treasury** deja de listar
`Bank` (sigue Effect, Remittance, Mandate, caja; BankAccount cuando toque).
Dependencias: lectura libre desde Treasury/Partners hacia Banks; escritura solo
vía Actions de Banks.
**Descartado:** poner Bank en Admin (Luis: dominio banks) o dejarlo en Treasury
(mezcla catálogo global con flujos de efectos).

### D2 — Preservar IDs y status del enriched JSON
**Elección:** Copiar/portar `banks.enriched.json` a `database/data/banks/` (o
equiv.); `BanksSeeder` upsert por `id`; map 1→`active`, 0→`inactive`; conservar
swift / swift_source / swift_updated_at si vienen. Fuera de `DatabaseSeeder`;
README § Seeders.
**Descartado:** set manual de “habituales” (opción B del explore).

### D3 — Status string + enum
**Elección:** Enum PHP Active/Inactive (patrón Currency/Geography) en columna
`string`. SoftDeletes.

### D4 — SWIFT pad a 11
**Elección:** Portar regla v1 en Create/Update: si `swift` no vacío y
`strlen < 11`, `str_pad(..., 11, 'X')`. Max 11 en validación. Campos
`swift_source` / `swift_updated_at`: en alta/edición manual UI, 20 puede setear
`swift_source=manual` y `swift_updated_at=now()` al cambiar swift, o dejar null
si no se toca — documentar en Action; seeder conserva valores del JSON.
**Descartado:** omitir pad (rompe BICs cortos vs v1).

### D5 — Validación campos
**Elección:** Create/Update: `name` required; `eu_code` required (como v1);
tradename/lei/supervisor_code/swift nullable; slug desde name. Sin inmutables
tipo Currency code/number. Unique: 10 decide si `eu_code` unique entre no
trashed (v1 no lo forzó en validator; preferible unique si no hay duplicados en
JSON — smoke en seed; si hay dupes, no unique y anotar).

### D6 — UI modales + Active-first
**Elección:**
- Rutas: `admin/banks`, `table`, store/update/destroy/status (URL Configuración).
- Pages Inertia bajo `Banks/` (dominio); Index ServerTable columnas útiles
  (name, tradename, eu_code, swift como mínimo); Acciones toggle→Edit→Delete;
  sin columna Estado.
- Create/Edit Dialog + Form kit (espejo Currencies).
- Default sorts vacío: Active-first + `name` (helper del polish Active-first o
  CASE local).
**Descartado:** páginas Create/Edit full-page de v1.

### D7 — Functionality + menú
**Elección:** Functionality `banks` en módulo `admin`; Spatie `banks.*`;
CONFIGURATION_SLUGS + route → Index banks; i18n Bancos.
**Descartado:** módulo SaaS `banks` activable aparte.

### D8 — Sin import en Index / sin jsonBanks
Seed Artisan/README only.

### D9 — Delete
Soft delete. Sin BankAccount aún; futuro bloqueo si hay FKs.

### D10 — E2E / vinculaciones
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | `banks` + `banks.*` |
| V-08 | Sí | Configuración → Bancos |
| V-09 | No | Módulo `admin` |
| R-TEN-05 | N/A | Global; test cross-company |
| E2E | N/A | Feature tests |
| V-01..V-06, resto | No | |

Ficha: `docs/modules/Banks.md` (playbook §1) + glosario Bank.

## Risks / Trade-offs

- [Nuevo dominio vs poco código] → Aislamiento limpio vs Treasury; ficha + mapa
  obligatorios en el mismo change.
- [eu_code unique] → Verificar JSON; si hay colisiones, no unique.
- [IDs preservados] → Solo BD limpia; no re-seed con FKs futuras.

## Migration Plan

1. Migración `banks` → Functionality → seeder → menú + README + module-map/ficha.
2. Rollback: quitar menú/Functionality; dropear tabla si no hay FKs.

## Open Questions

Ninguna bloqueante. Unique de `eu_code`: 20 confirma con datos del JSON en apply
(si colisión → sin unique, nota en task).
