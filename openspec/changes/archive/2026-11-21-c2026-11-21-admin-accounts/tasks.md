# Tasks

## 1. Datos y dominio Account

- [x] 1.1 Migration `accounts` (name, slug, description, rate
  decimal(12,2), duration nullable, status string, is_default bool,
  softDeletes) + model `Account` + enum `AccountStatus` + factory;
  verificar migrate fresca OK
- [x] 1.2 Action `SetDefaultAccount` (única default) + regla no-delete
  de la default; verificar unit/Feature de default y delete bloqueado

## 2. Backend HTTP / authz / menú

- [x] 2.1 Policy + permisos `accounts.*` (CreateFunctionality /
  RealignMenuCatalog + PermissionSeeder según patrón Unit); Functionality
  `accounts` bajo `configuracion`; verificar V-07/V-08 (menú Configuración
  → Cuentas)
- [x] 2.2 Controller Index/`table`/store/update/destroy/toggle + FormRequests
  + Resource; Active-first+name sin sorts; verificar Feature CRUD + 403
  sin permiso + V-21 módulo configuracion
- [x] 2.3 Ruta GET `admin/accounts/renewal` + página máscara ComingSoon;
  verificar 200 con `accounts.view` y 403 sin permiso

## 3. Frontend

- [x] 3.1 Pages Admin/Accounts Index + modales Create/Edit (campos name,
  description, rate, duration, status, is_default) + i18n es+en;
  verificar UI / assert fuente ServerTable y cero literales
- [x] 3.2 Página RenewalComingSoon + i18n; verificar copy próximamente

## 4. Seeder / registro / DoD

- [x] 4.1 `AccountsSeeder` con ≥1 Active `is_default=true`; documentar
  README seeders; Index no auto-importa; verificar seed idempotente
- [x] 4.2 Glosario `Account`, ficha Admin, module-map si aplica; V-01..V-20
  según design (V-07/08/20/21 sí; R-TEN-05 N/A global); E2E N/A
- [x] 4.3 Pint dirty + PHPStan + tests afectados en verde

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
- Siguiente change: `company_accounts` + ≤1 activa + caducidad → 403 +
  CTA a `/admin/accounts/renewal` + histórico.
