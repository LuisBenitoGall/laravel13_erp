# Proposal

## Why

Las companies necesitan **centros de trabajo** (dirección operativa) de la
empresa en sesión — base v1 `Workplace` — antes de pedidos, empleados y
obras. Aún no existen en v2.

## What Changes

- Entidad Admin `Workplace` con `BelongsToCompany` (siempre sesión).
- Dirección **embebida** (1 por centro): `address`, `cp`, `town_id`.
- MVP: name, dirección, `featured` (UI **Principal**; ≤1/company;
  default facturación/pedidos futuros), status (+ slug auto).
- Form Create/Edit: Dirección+CP en fila (75/25); Estado+Principal.
- CRUD: Index `<ServerTable>` + Create/Edit/Delete; Policy + Spatie
  `workplaces.*`.
- Functionality `workplaces` bajo Module `empresas` → menú Centros de
  trabajo → `/admin/workplaces`.
- Glosario, Admin.md, module-map (Workplace en Admin).
- **No** Address N; **no** campos v1 extra; **no** user_workplaces.

## Capabilities

### New Capabilities

- `workplaces`: CRUD tenant de centros de trabajo + dirección embebida.

### Modified Capabilities

- `menu-core-pillars`: subítem Centros de trabajo bajo `empresas`.

## Impact

- Migración/modelo/factory/enum; Actions; FormRequests; Controller;
  Policy; rutas admin.
- Pages Inertia Index/Create/Edit; cascada Country→Province→Town.
- Seed/Realign Functionality + menú; tests R-TEN-05.
- Docs: glossary, Admin.md, module-map.
