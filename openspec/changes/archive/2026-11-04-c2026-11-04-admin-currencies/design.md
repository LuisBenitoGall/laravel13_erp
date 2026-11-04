# Design

## Context

See proposal.md — Why. v1: tabla `currencies`, softDeletes, status int, CRUD Admin
+ DataTables + modales; Edit solo name/symbol; `Currency::import()` al abrir Index
(status siempre 0); dump Viudavila `CurrenciesTableSeeder` con IDs estables
(EUR=2, USD=5, …). v2: catálogos globales vía playbook `--global`; Countries como
plantilla ServerTable + Acciones + status string; Configuración SA vía
`MenuBuilder::CONFIGURATION_SLUGS`.

## Goals / Non-Goals

**Goals:**

- Una entidad global `Currency` en `app/Domains/Admin` con CRUD plano v1→v2.
- Functionality `currencies` + menú Monedas; seeder datos v1 con IDs estables y
  Active solo habituales.
- Policies + Spatie `currencies.*`; Index sin columna Estado (toggle en Acciones).

**Non-Goals:**

- Consumidores (CompanySettings, asientos, facturas); módulo SaaS nuevo; E2E
  browser; import automático al Index.

## Decisions

### D1 — Preservar IDs del dump v1
**Elección:** Seeder upsert por `id` desde dump Viudavila /
`storage/json/currencies.json` normalizado a filas con mismos IDs que
`CurrenciesTableSeeder` Viudavila (EUR id=2, USD id=5, GBP id=67, CHF id=95, …).
**Descartado:** IDs autoincrement nuevos — rompe FKs futuras (`company_settings`,
asientos, etc.).
**Impl:** JSON en `database/data/currencies/currencies.json` (o equivalente);
`CurrenciesSeeder` fuera de `DatabaseSeeder` (como Geography); README § Seeders.

### D2 — Status string + habituales Active
**Elección:** Enum PHP Active/Inactive (reutilizar patrón Countries /
`ModuleStatus`-like) en columna `string`. Seeder: códigos **EUR, USD, GBP, CHF**
→ `active`; resto → `inactive` (v1 importaba todo a 0).
**Descartado:** dejar todo Inactive (inútil para uso diario); Active = todos los
ISO (ruido); Active solo EUR/USD (demasiado estrecho para comercio UK/CH).
**Símbolos:** conservar los del dump; rellenar los del mapa v1
(`EUR→€`, `USD→$`, `AUD→$`, `CAD→$`) si vienen null; GBP/CHF pueden quedar null
hasta edición manual (no inventar en seeder salvo que el dump ya los traiga).

### D3 — symbol max 8
**Elección:** `string` nullable max **8** (brief; no `char(2)` v1). Create/Edit
validan max:8.
**Descartado:** char(2) — rechaza símbolos multi-byte / texto corto tipo `Fr.`.

### D4 — code / number inmutables en Edit
**Elección:** Update Action/FormRequest **no aceptan** code ni number (como v1
validator update). Create sí: name required; code max 3 unique entre no trashed;
number max 4 unique entre no trashed; symbol max 8; status default Active en alta
manual (o Switch en modal Create, patrón Countries).
**Descartado:** permitir editar code/number — rompe FKs semánticas ISO.

### D5 — UI plana (espejo Countries Index, sin drill)
**Elección:**
- Rutas: `admin/currencies`, `admin/currencies/table`, store/update/destroy/status.
- Index ServerTable: name, code, number, symbol; Acciones: toggle → Edit → Delete.
- Create/Edit Dialog + Form kit; **sin columna Estado**.
**Descartado:** página Edit full-page; columna Estado textual.

### D6 — Functionality + menú
**Elección:** Functionality `currencies` en módulo `admin` vía AdminCatalog /
CreateFunctionality; Spatie `currencies.*`; `MenuBuilder::CONFIGURATION_SLUGS` +
route map → Index currencies; label i18n Monedas.
**Descartado:** Functionality nueva de módulo SaaS.

### D7 — Sin import en Index
**Elección:** Seed explícito Artisan/README. Index **no** llama import.
**Descartado:** portar `Currency::import()` al Index (efecto lateral en GET).

### D8 — Delete
**Elección:** Soft delete. Sin hijos; si en el futuro hay FKs consumidoras, 20
bloquea delete con mensaje (hoy no hay consumidores en v2).

### D9 — E2E
N/A (`e2e.md` §4).

### D10 — Vinculaciones (playbook §6)
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06, V-10..V-20 | No | Catálogo maestro Admin |
| V-07 | Sí | Functionality `currencies` + `currencies.*` |
| V-08 | Sí | Configuración → Monedas |
| V-09 | No | Reutiliza módulo `admin` |
| R-TEN-05 | N/A | Global; test cross-company “mismo catálogo” |
| E2E | N/A | Feature tests CRUD + seed |

## Risks / Trade-offs

- [IDs preservados vs BD parcial] → Solo en BD limpia; documentar no re-seed si hay
  FKs consumidoras.
- [Habituales fijos en código] → Constante/array en seeder documentado en design;
  ampliar set = change futuro, no improvisar en apply.
- [Configuración solo SA hoy] → Misma frontera que Países/Modules.

## Migration Plan

1. Migración `currencies` → seed Functionality → seed monedas → menú + README.
2. Rollback: quitar menú/Functionality; dropear tabla si no hay FKs externas.

## Open Questions

Ninguna bloqueante.
