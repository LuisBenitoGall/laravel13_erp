# Tasks

## 1. Rutas y gate

- [x] 1.1 Registrar `admin.account.show` / table / `admin.account.renew` /
  `renew.store` bajo `/admin`; retirar `/account*` de web (redirect
  opcional); actualizar `MenuFunctionalityHref` profile →
  `admin.account.show`; verificar MenuCuenta tests
- [x] 1.2 Whitelist middleware + `renewUrl` → `admin.account.renew`;
  redirect/eliminar `admin.accounts.renewal`; verificar Feature 403 CTA
  y whitelist histórico/renew sin Active

## 2. UI histórico + renovación + grid

- [x] 2.1 Crear `Components/Account/AccountCatalogGrid` (props value/
  onChange + lista Active); verificar uso en Renew y export limpio
- [x] 2.2 Pages `Admin/Account/Show` (histórico + CTA) y
  `Admin/Account/Renew` (grid + POST RenewCompanyAccount); retirar
  `Pages/Account/*`; R-FE-04; i18n; verificar Feature show/renew/upgrade
  con Active y sin Active

## 3. DoD

- [x] 3.1 Ficha Admin / glosario si hace falta; E2E N/A; Pint + PHPStan +
  tests CompanyAccount/MenuCuenta/Account renewal en verde

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
- No Admin consumirá `AccountCatalogGrid` en change futuro.
