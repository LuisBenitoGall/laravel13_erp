# Proposal

## Why

El catálogo `Account` ya existe; falta el vínculo empresa↔cuenta
(`company_accounts`), la invariante de una Active, la caducidad con
bloqueo de acceso y la UI de histórico/renovación en Cuenta → Mi cuenta
(hoy placeholder).

## What Changes

- Modelo `CompanyAccount` / tabla `company_accounts` (sin `guardian`).
- Enum `CompanyAccountStatus`: Pending, Active, Expired, Cancelled.
- ≤1 Active por company; alta Company crea Active con Account default.
- Job diario de caducidad (Active + `end_date` pasada → Expired).
- Middleware de acceso: company sin Active vigente → 403 + mensaje + CTA
  a Mi cuenta; **whitelist** Mi cuenta/renovación (y select/logout) para
  poder renovar.
- Mi cuenta (`/account`): ServerTable histórico + renovación; deja de ser
  ComingSoon.
- `/admin/accounts/renewal` → redirect a Mi cuenta.
- Permisos: actores con acceso a Mi cuenta (`profile.view` + capas
  `cuenta`).

## Capabilities

### New Capabilities

- `admin-company-accounts`: vínculo, caducidad, gate 403, Mi cuenta.

### Modified Capabilities

- `menu-core-pillars`: Mi cuenta deja de ser placeholder; muestra
  histórico/renovación.

## Impact

- CreateCompany, middleware `company`, Schedule, Account/ComingSoon,
  tests R-TEN-05, V-16 cron, V-18 onboarding, ficha Admin, glosario.
- E2E: valorar flujo Mi cuenta (40); Feature cubre gate + renew.
