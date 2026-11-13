# Proposal

## Why

Varios prefijos URL (`/partners`, `/company`, `/profile`) y varios
`Domains/*` (Partners, Banks) añaden complejidad sin valor. Admin debe ser
el único dominio funcional y `/admin` el único prefijo de Functionalities:
un middleware, una convención, como en v1.

## What Changes

- Norma: **Admin = único dominio PHP** del ERP funcional; absorber
  Partners (Company) y Banks en Admin.
- Toda Functionality bajo **`/admin/...`**: Mis Empresas
  `/admin/companies`; company-roles y profile también bajo `/admin`.
- Eliminar prefijos `/partners` y `/company`. Corte limpio (sin
  redirects).
- Actualizar MenuBuilder, front, tests, specs y **standards**
  (`module-map`, fichas Partners/Banks → Admin).
- Shell fuera: login, onboarding, select/switch, dashboard.
- Invariante que importa: `CurrentCompany` + rol cliente|proveedor cuando
  aplique (sin cambiar reglas de tenancy).

## Capabilities

### New Capabilities

- `admin-sole-domain`: dominio único Admin + prefijo `/admin` para
  Functionalities; absorción Partners/Banks; corte limpio de rutas
  legacy.

### Modified Capabilities

- `company-create`: rutas/nombres `admin.companies.*` (ex partners).
- `company-edit`: idem + paths UI.
- `menu-core-pillars`: href Mis empresas y menciones `/profile` →
  `/admin/...`.
- `user-edit`: ficha self bajo `/admin/profile` (ex `/profile`).
- `shell-topbar`: enlace avatar a nueva URL profile.
- `banks-catalog`: deja de ser “dominio Banks”; vive en Admin.
- `account-placeholder`: deja de contrastar con `/profile` root si aplica.

## Impact

- PHP: mover namespaces `Domains/Partners/*`, `Domains/Banks/*` →
  `Domains/Admin/`; providers/policies/autoload; borrar carpetas vacías.
- Rutas: consolidar en grupo `admin`; quitar `partners.php` / prefix
  `company`.
- Front: hardcodes `/partners/...`, `/company/...`, `/profile` →
  `/admin/...`; pages bajo `Pages/Admin/...` si se mueven.
- Docs: `module-map.md`, `docs/modules/Partners.md` /
  `Banks.md` → absorción en `Admin.md`.
- Tests Feature que usen `partners.*` / `/profile` / `/company`.
