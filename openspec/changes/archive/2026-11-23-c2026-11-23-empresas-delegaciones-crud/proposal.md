# Proposal

## Why

Hace falta gestionar **Delegaciones** (sociedades hijas con NIF bajo una matriz)
desde Empresas, sin tratarlas como tenant de sesión ni asignarles
`company_accounts`. Hoy solo existe jerarquía genérica en Edit y el Create de
Company siempre crea cuenta SaaS; el switcher mezcla matrices e hijas.

## What Changes

- CRUD Delegaciones (Functionality bajo module `empresas`): Index ServerTable,
  Create/Edit diferenciados del Create de Mis empresas.
- Alta: Company con `parent_id` = matriz de sesión, NIF, ≥1 Workplace, creador
  en `user_companies` + roles como alta de empresa **sin**
  `AssignDefaultCompanyAccount` / sin fila `company_accounts`.
- Switcher y selección de empresa de sesión: solo matrices (`parent_id` null);
  rechazar elegir una delegación como CurrentCompany.
- Menú: ítem Delegaciones. Sin tabla `delegaciones` nueva.

## Capabilities

### New Capabilities

- `company-delegations`: CRUD de Companies hijas como Delegaciones (alta sin
  cuenta SaaS, workplace mínimo, perímetro matriz de sesión).

### Modified Capabilities

- `admin-company-accounts`: excepción — alta Delegación no crea vínculo Active.
- `shell-topbar`: `available_companies` / switcher solo matrices.
- `menu-core-pillars`: ítem Delegaciones bajo module `empresas`.
- `company-hierarchy`: Delegación nace con `parent_id` fijado a la matriz de
  sesión (complementa el tab Jerarquía que no crea companies).

## Impact

- Actions `CreateDelegation` (o equivalente), controllers/routes/policies,
  RealignMenuCatalog + permisos, Inertia pages, Workplace en el alta.
- Shared data del switcher / `companies.select` / `companies.switch`.
- Tests Pest: alta sin account, switcher, listado solo hijas de sesión.
- E2E §4: N/A (flujo docs no catalogado para este CRUD).
- Oleadas futuras (pedidos sociedad, listados grupo, N:M workplace): fuera.
