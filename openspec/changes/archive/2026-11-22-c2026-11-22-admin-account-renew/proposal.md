# Proposal

## Why

Las rutas de Mi cuenta / renovación quedaron fuera de `/admin` (`/account`)
y mezclan histórico con renovación. Hay que alinearlas al perímetro
admin, separar histórico vs renovación/upgrade, dejar un grid de
catálogo reutilizable (futuro No Admin) y que el 403 apunte a renovar.

## What Changes

- Histórico: `GET /admin/account` (Cuenta → Mi cuenta) + CTA Renovar/
  Mejorar.
- Renovación: `GET|POST /admin/account-renew` (formulario básico +
  `AccountCatalogGrid`).
- Quitar `/account` (web) y redirigir leftovers; actualizar menú
  `profile` href, whitelist middleware y CTA de Acceso bloqueado →
  renovación.
- Componente reutilizable de selección de Accounts Active.
- Upgrade permitido con Active vigente; sin Active solo whitelist
  histórico/renovación (+ select/logout).

## Capabilities

### New Capabilities

_(ninguna — delta sobre company-accounts / menú / form-kit opcional)_

### Modified Capabilities

- `admin-company-accounts`: rutas admin, split histórico/renovación, grid.
- `menu-core-pillars`: Mi cuenta → `/admin/account`.

## Impact

- Routes web/admin, AccountController(s), MenuFunctionalityHref,
  middleware, pages Account/* → Admin/Account/*, tests CompanyAccount /
  MenuCuenta, ficha Admin.
- E2E: N/A (Feature).
