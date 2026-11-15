# Proposal

## Why

Con sectores y atribución Company↔Sector listos, hace falta un **Directorio**
bajo Empresas para descubrir companies Active (NIF + ≥1 sector) filtrando
por sector (OR), con UI tipo escaparate (aside + grid), no Mis empresas.

## What Changes

- Functionality `company-directory` bajo Module `empresas` →
  `/admin/company-directory` (+ Show informativo).
- Menú: Directorio junto a Mis empresas; `.view` sembrado a roles básicos
  (fricción ≈ solo `empresas.access`).
- Index: aside sectores Active (multi OR) + grid cards (sort name /
  recientes + paginación).
- Universo: Active + NIF presente + ≥1 sector; sin `is_public`.
- Card → Show solo lectura; sin CTA contactar/cliente-proveedor.
- **Excepción ServerTable** (grid de descubrimiento).
- NonGoal: CustomerProvider / contactar (change siguiente).

## Capabilities

### New Capabilities

- `company-directory`: Index grid + filtros sector OR + Show informativo
  + menú.

### Modified Capabilities

- `menu-core-pillars`: subítem Directorio bajo `empresas`.

## Impact

- Rutas/controller/Policy (viewAny directorio ≠ forUser Mis empresas).
- MenuBuilder + Realign/seed Functionality + grant `.view` básicos.
- Pages Inertia Directory Index/Show; i18n.
- Tests Feature: universo, OR, menú, Show sin CTA.
- Docs Admin.md; excepción en design/frontend note.
