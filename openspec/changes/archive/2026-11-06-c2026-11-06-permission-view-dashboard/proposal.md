# Proposal

## Why

El producto trata `{functionality}.view` como acceso al dashboard/Index (y
menú), y `{functionality}.viewAny` como detalle de un registro. El código y el
kernel siguen la convención Laravel invertida (`viewAny` = listado). Hay que
alinear constitución + menú + Policies antes del hardening de roles.

Fuente: `docs/prompts/c2026-11-06-permission-view-dashboard.md`.

## What Changes

- Kernel / architecture / adaptadores: documentar semántica producto
  (`.view` = dashboard/menú; `.viewAny` = show).
- `MenuBuilder`: AND comodín + `.view` (no `.viewAny`).
- Policies: método Laravel `viewAny()` comprueba Spatie `.view`; `view()`
  comprueba Spatie `.viewAny`. Sufijos en BD sin renombrar.
- Controllers/gates ad-hoc que usen `.viewAny` para Index → `.view`.
- Specs menú + escenarios Index que citen `viewAny` para dashboard → `.view`.
- Tests de menú/Index/authz alineados.
- **No** incluye hardening roles (copy tenant, quitar AND manager en Index
  roles, etc.) — change 2.

## Capabilities

### New Capabilities

- `permission-view-semantics`: semántica canónica `.view` / `.viewAny` del ERP.

### Modified Capabilities

- `menu-visibility`: menú AND usa `.view`.
- `menu-core-pillars`: visibilidad Cuenta (`profile` / `company-modules`) con
  `.view`.
- Index authz → `.view`: `admin-geography-catalog`, `admin-currency-catalog`,
  `admin-units-catalog`, `admin-vat-types-catalog`,
  `admin-accounting-account-types-catalog`, `banks-catalog`,
  `admin-permission-catalog`, `company-users-index`, `users-directory`.
  (Otros Index que fallen en grep de tests se alinean en el mismo apply.)

## Impact

- `docs/standards/kernel.md`, `architecture.md` (R-AUT menú), rules/adaptadores
  que citen menú+viewAny.
- `MenuBuilder`, Policies `viewAny`/`view`, algunos controllers.
- Feature tests menú/visibilidad/catálogos.
- E2E: no aplica salvo que algún browser asuma el permiso antiguo (revisar;
  no añadir suite nueva).

## Excepciones a standards

Este change **corrige** el estándar (kernel/architecture): la excepción es que
el estándar actual está mal respecto al producto; se actualiza en el mismo
change (no se improvisan excepciones permanentes).
