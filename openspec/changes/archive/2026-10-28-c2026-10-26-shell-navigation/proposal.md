## Why

El shell de Fase 0 existe pero el aside llega vacío o incompleto: no hay logo ERP ni
Dashboard fijo; Super Admin no ve Configuración si `admin` no está encendido; y al
entrar a `/admin/*` recibe `403 Module [admin] is not active…` porque
`EnsureModuleIsActive` ignora el rol SA. Además `PostLoginRedirect` fuerza a SA hacia
AdminOnly. Hay que alinear menú, middleware y seed (ACME) al kernel (SA con company,
mismo flujo de pertenencia).

## What Changes

- Aside: logo ERP + logo company + menú.
- Dashboard siempre en menú.
- Super Admin: bloque Configuración (AdminOnly/operador) siempre en menú; unificar el
  split «Plataforma» bajo Configuración.
- Bypass `module:` / `EnsureModuleIsActive` para Super Admin en módulos **AdminOnly**
  (sin ampliar perímetro de datos).
- Post-login SA = mismo destino que el resto (Dashboard / selección); sin company →
  alta de empresa.
- Seed: Super Admin (config) + company ACME NIF `00000000A`.
- Menú tenant (no SA) sigue R-AUT-08 para Basic/Optional.
- **No** maestros Fase 1 ni Tienda/Consultas.

## Capabilities

### New Capabilities

- `shell-navigation`: logos, Dashboard fijo, Configuración SA, layout aside.

### Modified Capabilities

- `menu-visibility`: excepciones Dashboard + Configuración SA; bypass middleware
  AdminOnly para SA.
- `company-tenancy` / auth onboarding (si aplica): SA sin company → crear empresa;
  post-login unificado.
- Seed/demo (vía tasks; puede tocar spec de admin-ui-login o tenancy si menciona demo).

## Impact

- `AppLayout.tsx`, `MenuBuilder`, `EnsureModuleIsActive` /
  `CompanyModuleActivationChecker`, `PostLoginRedirect`
- Seeders (`AdminCatalogSeeder` y/o `UserSeeder`)
- Shared Inertia props (`HandleInertiaRequests`)
- `lang/*`, tests Feature (menú, 403, seed, redirect)
- Posible delta `docs/modules/Admin.md` / kernel note en design
