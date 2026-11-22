<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-admin-account-renew
Rule: 10-architecture
Summary: Rutas cuenta bajo `/admin`; histórico en `/admin/account` con
  CTA renovar/upgrade; renovación en ruta semántica tipo
  `/admin/account-renew`; grid catálogo reutilizable; sin Active no se
  navega el ERP (vista Acceso bloqueado + CTA).
Goals:
- Mover / alinear rutas de Mi cuenta al prefijo **`/admin`** (nada de
  `/account` fuera de admin). Histórico = `/admin/account` (o slug que
  fije 10, semántica obvia).
- Renovación / upgrade = ruta dedicada con valor semántico obvio
  (p. ej. `/admin/account-renew`); 10 fija el name de ruta.
- Menú **A**: un solo ítem Cuenta → Mi cuenta → histórico; el histórico
  expone CTA «Renovar / Mejorar» hacia la ruta de renovación.
- Vista de renovación **siempre** usable para empresas con cuentas
  previas (upgrade aunque haya Active vigente).
- Empresas **sin** cuenta Active vigente (sin filas o caducada): al
  navegar el ERP ven la misma vista «Acceso bloqueado» (mensaje + CTA
  «Ir a renovar cuenta»); sin Active **no** se navega el resto del ERP.
  Whitelist: histórico y/o renovación (y select/logout según design)
  para poder crear/renovar.
- Formulario de renovación **básico** por ahora: opciones de plantilla
  Account del catálogo (default preseleccionado) hasta el entorno No
  Admin.
- Grid/selector de cuentas disponibles del catálogo = **componente
  reutilizable** (`AccountCatalogGrid` o nombre que fije 10) para Admin
  ahora y para registro de empresa nueva en No Admin después.
- Actualizar whitelist del middleware y menú/`profile` hrefs al nuevo
  `/admin/...`.
NonGoals:
- Entorno No Admin completo (solo dejar el grid reutilizable listo).
- Cashier/Stripe / cobro real.
- Cambios al catálogo Configuración → Cuentas salvo consumirlo.
- `guardian`.
- Rediseño ilustrado del 403 (usuario OK con la vista actual de Acceso
  bloqueado).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
Notes:
- Origen: `/opsx-explore` tras company-accounts. Decisiones: (1) menú A
  histórico + CTA; (2) sin Active = misma vista bloqueo + crear/renovar;
  rutas bajo `/admin`; grid reutilizable.
- Hoy existe `/account` + ComingSoon/Show y máscara
  `admin/accounts/renewal` — este change realinea.
- R-FE-04 en tablas numéricas/fechas del histórico.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Histórico bajo /admin/account con CTA renovar
El sistema SHALL exponer el histórico de `company_accounts` de la
empresa en sesión en una ruta admin (p. ej. `/admin/account`) enlazada
desde Cuenta → Mi cuenta, con CTA hacia la renovación/upgrade.

#### Scenario: Abrir Mi cuenta con histórico
- **WHEN** un usuario con acceso a Mi cuenta y empresa en sesión abre
  Mi cuenta
- **THEN** ve el histórico ServerTable y un CTA para renovar o mejorar

### Requirement: Renovación en ruta admin semántica
El sistema SHALL exponer la renovación/upgrade de cuenta en una ruta
admin dedicada de semántica obvia (p. ej. `/admin/account-renew`), con
formulario básico sobre plantillas Account Active del catálogo, usable
también con cuenta Active vigente (upgrade) y cuando no hay Active
(crear/renovar tras bloqueo).

#### Scenario: Renovar o upgrade
- **WHEN** el actor confirma una plantilla Account en la vista de
  renovación
- **THEN** queda el vínculo company_accounts según ≤1 Active

#### Scenario: Acceso a renovación sin Active vigente
- **WHEN** la empresa no tiene cuenta Active vigente y el actor sigue el
  CTA desde Acceso bloqueado
- **THEN** puede abrir la renovación y completar alta/renovación

### Requirement: Grid catálogo de Accounts reutilizable
El sistema SHALL exponer un componente reutilizable de selección/grid
de Accounts del catálogo (Active) usable en la renovación admin y
previsto para el alta de empresa en No Admin.

#### Scenario: Selección en renovación
- **WHEN** el actor está en la vista de renovación
- **THEN** elige entre Accounts Active mediante el componente grid/
  selector reutilizable


## MODIFIED Requirements

### Requirement: Sin Active no se navega el ERP
Sin cuenta Active vigente, el sistema SHALL seguir mostrando la vista
de Acceso bloqueado (mensaje + CTA a renovar) al intentar navegar el
ERP, permitiendo solo las rutas necesarias para histórico/renovación
(y select/logout según design). Las rutas de cuenta viven bajo `/admin`.

#### Scenario: Navegar con cuenta caducada o ausente
- **WHEN** el actor intenta abrir Dashboard u otra ruta ERP sin Active
  vigente
- **THEN** ve Acceso bloqueado con CTA a renovar/crear cuenta
