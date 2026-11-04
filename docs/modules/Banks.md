# Dominio Banks — catálogo global de bancos

> Ficha playbook §1. Change `c2026-11-04-banks`.

## Propósito y alcance

**Entra:** maestro global `Bank` (sin `company_id`): name, slug, tradename, lei,
`eu_code`, `supervisor_code`, swift, `swift_source`, `swift_updated_at`; soft delete;
status Active/Inactive (enum PHP + columna string); CRUD bajo Configuración → Bancos
(ServerTable + modales); seeder desde enriched v1 con IDs y status preservados;
Functionality `banks` + Spatie `banks.*` (módulo SaaS `admin`).

**No entra:** `BankAccount` / cuentas de partners o empresa; sync SWIFT automático
(`BanksSyncSwift`); regenerar JSON al store/update; import al abrir Index; tenancy;
módulo SaaS `banks` activable aparte.

## Entidades

| Pieza | Notas |
|---|---|
| `Bank` | Catálogo global (`app/Domains/Banks`); softDeletes; Auditable |
| `BankStatus` | `active` / `inactive` |
| `BanksSeeder` | `database/data/banks/banks.enriched.json`; fuera de `DatabaseSeeder` |
| Index | Columnas name, tradename, eu_code, swift; Acciones toggle→Edit→Delete; sin columna Estado; default Active-first + name |
| SWIFT | Pad a 11 con `X` si corto; UI max 11; al cambiar en CRUD → `swift_source=manual` + `swift_updated_at=now()` |
| `eu_code` | Required + unique entre no trashed |

## Vinculaciones

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality `banks` + Spatie `banks.*` |
| V-08 | Sí | Configuración → Bancos |
| V-09 | No | Reutiliza módulo `admin` |
| R-TEN-05 | N/A | Catálogo global; test cross-company “mismo dato” |
| E2E | N/A | Feature tests |

## Dependencias

Banks → Admin (Functionality/menú). Lectura libre desde Treasury/Partners hacia Banks;
escritura solo vía Actions de Banks (`module-map.md`).
