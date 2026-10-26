<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-26-platform-console-mvp
Rule: 10-architecture
Summary: Último change del cierre de Fase 0: consola de plataforma MVP para el operador
SaaS (Super Admin) — listado de tenants/companies del perímetro vinculado, acceso claro a
elegibilidad de módulos, sin facturación ni “ver todas las companies” sin vínculo. Cierra
el shell fundacional antes de Fase 1.
Goals:
- Consola operador usable y separada conceptualmente del admin de cada empresa: punto(s)
  de entrada de menú para Super Admin (tenants + elegibilidad; roles de plataforma ya
  existen).
- Listado de companies/tenants para el operador con ServerTable (o listado equivalente
  del catálogo), limitado al **perímetro de companies vinculadas** al Super Admin
  (kernel: no bypass de datos; no “ver todas” salvo change explícito futuro).
- Elegibilidad de módulos (autorizar/revocar Optional/AdminOnly según reglas vigentes):
  la UI `company-module-authorizations` ya existe — este change MUST alinearla al
  perímetro vinculado (hoy el index carga companies Active sin filtrar por vínculo) y
  dejar el flujo operador coherente (p. ej. ir de tenant → autorizaciones).
- Respetar tres capas y sync manager ya existentes; no reinventar Authorize/Revoke.
NonGoals:
- Laravel Cashier / Stripe / planes de pago / cuotas.
- RGPD export/borrado, backups por tenant, observabilidad SaaS.
- “Ver todas las companies” del sistema sin estar vinculado (prohibido por kernel salvo
  change explícito de producto — **no** es este MVP).
- Engorde maestro Company (CustomerProvider, obras, etc. = Fase 1).
- Form kit (ya B); CRUD roles (ya hecho).
- Onboarding asistente largo (series/plan contable…) — onboarding mínimo ya existe.
Skills:
- tenancy-rbac
- laravel-specialist
- laravel-patterns
- frontend-design
Notes:
- Cierre Fase 0: A y B archivados → **C este change** → Fase 0 cerrada → maestros.
- Visión §4.5 “consola de plataforma”; MVP acordado: tenants + elegibilidad; sin Stripe.
- Piezas ya existentes a reutilizar/endurecer: `CompanyModuleAuthorizationController` +
  página Index; `PlatformRoleController`; Partners `CompanyController` (tenant). 10
  decide si el listado operador es ruta Admin/Platform nueva o reusa Partners con gate
  SA — sin inventar dominio Platform completo si no hace falta.
- Hallazgo explore: index de authorizations hace `Company::query()->where Active` sin
  filtrar `user_companies` del SA → corregir como parte del perímetro.
- i18n snake_case; tests Feature perímetro + authorize/revoke; E2E N/A salvo que 10
  marque un flujo de e2e.md §4 (no debería).


## ADDED Requirements

### Requirement: Consola plataforma MVP (operador Super Admin)
El sistema SHALL exponer una experiencia de operador (Super Admin) para listar las
companies a las que está vinculado y gestionar la elegibilidad de módulos de esas
companies. SHALL NOT listar ni operar companies ajenas al vínculo del Super Admin.
SHALL NOT incluir facturación SaaS ni cuotas en este change.

#### Scenario: Listado de tenants del perímetro
- **WHEN** Super Admin abre el listado de tenants/companies de la consola plataforma
- **THEN** solo ve companies vinculadas a su usuario (vía `user_companies`)

#### Scenario: Acceso a elegibilidad desde el perímetro
- **WHEN** Super Admin elige una company de su perímetro para gestionar autorizaciones
  de módulo
- **THEN** puede autorizar o revocar según las Actions/reglas ya vigentes de las tres
  capas

#### Scenario: Company no vinculada inaccesible
- **WHEN** Super Admin intenta abrir elegibilidad (o ficha operador) de una company a la
  que no está vinculado
- **THEN** la operación se rechaza (403/404)

#### Scenario: Sin billing en el MVP
- **WHEN** se revisa el entregable de este change
- **THEN** no hay Cashier/Stripe ni pantallas de plan de pago


## MODIFIED Requirements

### Requirement: (elegibilidad / listados Admin existentes)
Si el spec global de company-module-layers o admin UI describe el listado de companies
en authorizations sin perímetro: SHALL quedar explícito que Super Admin solo opera
companies vinculadas. 10 ajusta el delta al spec existente correcto
(`company-module-layers` / UI admin) sin ampliar a “ver todas”.
