# Design

## Context

See proposal.md — Why. v1: `iva_types` (name, iva float, equivalence_surcharge,
status, softDeletes), Index + modales, import JSON sin país. v2: Countries ya
existen (España id=66); patrón Currencies/Units; Configuración
`CONFIGURATION_SLUGS`. Brief: country obligatorio; listado plano+columna País;
RE nullable; seed España; Admin no Tax folder.

## Goals / Non-Goals

**Goals:**

- `VatType` global en Admin con FK Country; CRUD Configuración; seeder ES;
  Active-first.

**Non-Goals:**

- IvaTypeCustomer/Provider; consumidores factura/producto; carpeta
  `app/Domains/Tax`; tenancy; E2E.

## Decisions

### D1 — Admin + slug `vat-types`
**Elección:** Código en `app/Domains/Admin`; Functionality slug `vat-types`;
permisos `vat-types.*`; rutas `admin/vat-types*`; tabla `vat_types`. module-map:
VatType listado en Admin (catálogo Configuración); Tax **consume** (lectura) en
fases posteriores — no mover CRUD a Tax ahora.
**Descartado:** implementar ya dominio Tax completo.

### D2 — Columnas rate / equivalence_surcharge
**Elección:** `rate` decimal(**5,2**) required (R-BD-02 contable 2 decimales;
valores tipo 21.00, 10.00). `equivalence_surcharge` decimal(5,2) **nullable**.
Casts `decimal:2`. Name string required. Status string enum Active/Inactive.
**Descartado:** float v1; 3 decimales de venta (no aplica a % fiscal maestro).

### D3 — country_id required + selector completo
**Elección:** FK `countries.id`, not null. Form select / combo con **todos** los
Country (incl. Inactive y no trashed). Index: columna país (name) + filtro por
`country_id`. Soft-delete de Country: si hay VatType no trashed, **bloquear**
delete de Country o documentar restrict FK (20: preferir restrict + mensaje).
**Descartado:** país opcional; filtrar selector solo Active.

### D4 — Unique
**Elección:** Unique compuesto (`country_id`, `name`) entre no soft-deleted
(mismo nombre puede existir en otro país). No unique global de rate (varios
países con 21%).
**Descartado:** unique solo name global.

### D5 — UI
Index ServerTable: country (filtro), name, rate, equivalence_surcharge;
Acciones toggle→Edit→Delete; modales Create/Edit; sin columna Estado; sorts
vacío → Active-first + name.
**Descartado:** drill por país (opción B).

### D6 — Seeder España
JSON/`iva-types.json` → filas con `country_id=66`; map status 1/0 →
active/inactive; preservar ids del JSON. Requiere GeographySeeder previo.
`VatTypesSeeder` fuera de DatabaseSeeder; README. Si España no existe → fallar
claro.
**Descartado:** seed vacío.

### D7 — Delete / soft delete
Soft delete VatType siempre (historial). No purge físico en UI.

### D8 — Vinculaciones / E2E
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | `vat-types` + permisos |
| V-08 | Sí | Configuración → Tipos de IVA |
| V-09 | No | Módulo `admin` |
| V-13 | Parcial | Maestro fiscal base; sin SII aún |
| R-TEN-05 | N/A | Global; test cross-company |
| E2E | N/A | Feature tests |

## Risks / Trade-offs

- [Orden seed Geography → VatTypes] → Documentar en README.
- [IDs v1 vs país nuevo] → IDs de tipo se preservan; country_id es v2.
- [Inactive countries en select] → Intencional (brief).

## Migration Plan

1. Migración `vat_types` → Functionality → seeder (tras geografía) → menú.
2. Rollback: menú/Functionality; dropear si no hay FKs consumidoras.

## Open Questions

Ninguna.
