# Design

## Context

Hoy: `/account` (Show histórico + modal renew), whitelist
`account.*`, 403 CTA → `account.show`, `admin.accounts.renewal` redirect
a show. Brief: todo bajo `/admin`, split histórico/renovación, grid
reutilizable. Motivación: proposal — Why.

## Goals / Non-Goals

**Goals:** rutas admin, CTA histórico→renew, grid reutilizable, 403→renew,
upgrade con Active OK.

**Non-Goals:** No Admin completo, Stripe, ilustración 403, guardian,
catálogo Configuración.

## Decisions

### D1 — Rutas canónicas

| Uso | Method | URI | Name |
|---|---|---|---|
| Histórico Mi cuenta | GET | `/admin/account` | `admin.account.show` |
| Table histórico | GET | `/admin/account/company-accounts/table` | `admin.account.company-accounts.table` |
| Renovación UI | GET | `/admin/account-renew` | `admin.account.renew` |
| Confirmar renew | POST | `/admin/account-renew` | `admin.account.renew.store` |

- Prefijo `admin.` + middleware `auth` + `company` + module `cuenta` (o
  el grupo admin que ya use profile/account).
- **Eliminar** rutas `account.show` / `account.renew` / table en `web.php`.
- `admin.accounts.renewal` (máscara catálogo): redirect a
  `admin.account.renew` o eliminar si ya no se enlaza.
- Conflictos: resource plural `/admin/accounts` = catálogo SA; singular
  `/admin/account` = SaaS company — OK.

### D2 — Menú A

- Functionality `profile` href → `route('admin.account.show')`.
- Un solo ítem «Mi cuenta»; CTA en la página hacia
  `admin.account.renew` (i18n renovar/mejorar).

### D3 — Páginas

- `Admin/Account/Show` — histórico ServerTable (R-FE-04) + CTA.
- `Admin/Account/Renew` — usa `AccountCatalogGrid`; POST renew
  (reutiliza `RenewCompanyAccount`).
- Borrar o dejar de usar `resources/js/Pages/Account/*` tras migración.

### D4 — `AccountCatalogGrid`

- Ubicación: `resources/js/Components/Account/AccountCatalogGrid.tsx`
  (export estable; no acoplado a TipTap ni a Inertia page concreta).
- Props mínimas: `accounts: {id,name,rate,duration,is_default}[]`,
  `value` / `onChange` (account_id), disabled opcional.
- UI: grid de cards seleccionables (básico); sin cobro.
- Consumidor actual: Renew; futuro: No Admin registro empresa.

### D5 — Middleware / 403

- Whitelist names: `admin.account.show`, `admin.account.company-accounts.table`,
  `admin.account.renew`, `admin.account.renew.store`, + select/switch/
  logout/onboarding/locale (como hoy).
- `renewUrl` en `CompanyAccountRequired` → `admin.account.renew`.
- Mensaje i18n puede seguir hablando de renovar/crear cuenta.

### D6 — Authz

- GET histórico / renew: `profile.view` (o policy CompanyAccount viewAny
  alineada a quien ve Mi cuenta).
- POST renew: `profile.update` o `create` CompanyAccount (mantener
  patrón actual RenewCompanyAccountRequest; no inventar Functionality).

### D7 — Specs

- MODIFIED requisitos de `admin-company-accounts` (rutas + CTA + grid).
- MODIFIED `menu-core-pillars` Mi cuenta → `/admin/account`.

### D8 — Vinculaciones / E2E

- V-08: actualizar href menú (no ítem nuevo).
- E2E N/A; Feature MenuCuenta + CompanyAccount gate/renew.

## Risks / Trade-offs

- [Confusión accounts vs account] → docs + names `admin.account.*`.
- [Bookmarks /account] → redirect 301/302 a admin.account.show opcional
  en web.php una release.

## Migration Plan

1. Rutas admin + redirects; actualizar whitelist y menú.
2. Pages Show/Renew + AccountCatalogGrid; retirar Pages/Account.
3. Tests + i18n CTA.
4. Pint / PHPStan / tests afectados.

## Open Questions

_(ninguna)_
