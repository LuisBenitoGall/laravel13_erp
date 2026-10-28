<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-26-shell-navigation
Rule: 10-architecture
Summary: Completar el shell de navegación (aside + topbar): logos ERP y empresa, Dashboard
siempre visible, menú tenant por capas, Configuración siempre para Super Admin (AdminOnly)
con acceso real a rutas (bypass `module:admin`). Unificar el flujo de entrada del SA al
del resto (≥1 company; si no, ir a crear empresa). Seed de trabajo: Super Admin + ACME
(NIF 00000000A).
Goals:
- Aside, de arriba abajo: (1) espacio logo del ERP, (2) espacio logo de la empresa en
  sesión si existe, (3) menú.
- Ítem **Dashboard** siempre presente para cualquier usuario dentro del ERP (aunque no
  haya más módulos elegibles).
- Omitir por ahora Tienda y Consultas (v1).
- Estructura de menú orientada a referencia Viuda Vila: acceso a datos de **cuenta**,
  luego **empresa**, luego (según rol) resto; los maestros de Configuración
  (IVA, países, monedas, bancos, unidades, …) se irán enlazando al existir — este change
  no implementa esos maestros.
- **Super Admin**: bloque **Configuración** siempre visible, con todos los submenús
  propios del operador / AdminOnly del catálogo actual (modules, functionalities,
  companies, company-modules, company-module-authorizations, roles de plataforma, etc.).
  No debe depender de que el módulo `admin` esté autorizado+encendido para la company de
  sesión. El agrupado interno previo «Plataforma» se unifica bajo Configuración (o label
  equivalente acordado en UI).
- **Super Admin y rutas AdminOnly**: no debe recibir 403 de `EnsureModuleIsActive` /
  `module:admin` por falta de activación en la company (hallazgo: error actual
  «Module [admin] is not active for the current company»). Bypass de middleware de módulo
  para SA en AdminOnly; el perímetro de **datos** sigue siendo solo companies vinculadas.
- **Mismo flujo de entrada que el resto**: Super Admin también necesita ≥1 company activa
  vinculada para entrar al ERP; si no tiene, redirect a donde pueda **crear** una empresa
  (onboarding/alta existente), no un carril especial solo por permisos. Post-login: mismo
  destino razonable que el resto (p. ej. Dashboard / selección de empresa), **no** forzar
  `admin.modules.index` como atajo que rompe si admin no está encendido.
- **Seed de trabajo**: primer usuario Super Admin (credenciales de config/env como ahora)
  vinculado a company **ACME**, NIF ficticio **00000000A** (ajustar/reemplazar el seed
  demo actual si aplica; 10 delimita UserSeeder vs ampliar AdminCatalogSeeder).
- Seguir respetando Basic / Optional / AdminOnly para el menú **tenant** (no SA): capas
  Active + autorizado + encendido + permisos, salvo las excepciones de shell (Dashboard;
  Configuración + rutas AdminOnly solo SA).
- Ajustar `AppLayout` + `MenuBuilder` + `PostLoginRedirect` + checker/middleware; i18n
  snake_case.
NonGoals:
- Implementar entidades/maestros Fase 1 (IVA, países, monedas, bancos, unidades, …).
- Tienda, Consultas, Dashboard rico (KPIs): basta entrada Dashboard a página/home existente
  o placeholder mínimo.
- Cashier / billing.
- “Ver todas las companies” sin vínculo.
- Rediseño visual completo tipo shadcn-admin; foco navegación + logos + reglas de menú +
  seed/flujo SA.
Skills:
- tenancy-rbac
- frontend-design
- laravel-specialist
Notes:
- Prerrequisito: Fase 0 cierre (incl. platform-console-mvp) archivado; layout esqueleto ya
  existe pero `menu` puede llegar `[]` y no hay logo ERP ni Dashboard fijo ni Configuración
  SA sin capas; rutas `/admin/*` exigen `module:admin`.
- Kernel confirmado: User sin company no entra al ERP; SA bypasa permisos/módulos en Gate,
  no el perímetro de datos; Luis confirma SA debe ceñirse al mismo flujo de pertenencia.
- Hallazgo: `PostLoginRedirect` hoy manda SA a `admin.modules.index` → 403 si admin no
  activo en company; `CompanyModuleActivationChecker` no contempla SA.
- Clasificación módulos: Basic / Optional / AdminOnly. Configuración = AdminOnly/operador,
  solo SA, siempre (menú + acceso ruta).
- Referencia visual v1 Viuda Vila (aside); stack v2 Inertia/React.
- E2E: si toca flujo login+menú de e2e.md §4, 10 deja task; Feature: SA sin company →
  redirect crear; SA con ACME → dashboard/menú Configuración; GET admin.* sin 403 módulo.


## ADDED Requirements

### Requirement: Aside con logos y Dashboard fijo
El sistema SHALL mostrar en el aside, en este orden: espacio para logo del ERP, espacio
para logo de la company de sesión si existe, y el menú. SHALL incluir siempre un ítem
Dashboard para usuarios autenticados dentro del ERP.

#### Scenario: Orden del aside
- **WHEN** un usuario entra al ERP con AppLayout
- **THEN** ve zona logo ERP, zona logo empresa (o vacío si no hay logo), y debajo el menú

#### Scenario: Dashboard siempre
- **WHEN** el menú se construye para un usuario con sesión de empresa (o acceso ERP)
- **THEN** existe la entrada Dashboard aunque no haya otros módulos elegibles

---

### Requirement: Configuración y acceso AdminOnly siempre para Super Admin
El sistema SHALL, si el usuario tiene rol Super Admin, incluir en el menú un bloque
Configuración con las funcionalidades de operador/AdminOnly del catálogo (las existentes
hoy bajo admin / consola plataforma), sin exigir que el módulo admin esté autorizado y
encendido para la company de sesión. SHALL permitir a Super Admin acceder a las rutas
protegidas con `module:` de módulos AdminOnly sin 403 por módulo inactivo en la company.
SHALL NOT mostrar el bloque Configuración operador a quienes no sean Super Admin.
SHALL NOT ampliar el perímetro de datos: solo companies vinculadas.

#### Scenario: SA ve Configuración sin módulo admin encendido
- **WHEN** un Super Admin tiene company de sesión cuyo módulo admin no está encendido
- **THEN** el menú incluye Configuración con los submenús operador existentes

#### Scenario: SA no recibe 403 module:admin
- **WHEN** un Super Admin con company de sesión navega a una ruta `/admin/*` con
  middleware `module:admin` y admin no está activo para esa company
- **THEN** la petición no aborta 403 por módulo inactivo (sí aplican auth y company)

#### Scenario: No SA sin Configuración operador
- **WHEN** un usuario sin rol Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración de operador AdminOnly

---

### Requirement: Super Admin con el mismo flujo de pertenencia a company
El sistema SHALL exigir a Super Admin, igual que al resto, ≥1 company activa vinculada
para usar el ERP. SHALL, si un Super Admin autenticado no tiene company vinculada,
redirigirlo al flujo de alta/creación de empresa (no a un atajo AdminOnly). SHALL hacer
que el post-login / post-selección de empresa de Super Admin no fuerce un destino AdminOnly
distinto que provoque 403; destino alineado al del resto (p. ej. Dashboard).

#### Scenario: SA sin empresa → crear empresa
- **WHEN** un Super Admin inicia sesión sin companies activas vinculadas
- **THEN** es redirigido a donde puede crear una company (onboarding/alta existente)

#### Scenario: SA con empresa → flujo normal
- **WHEN** un Super Admin con ≥1 company activa entra al ERP
- **THEN** sigue selección/sesión de empresa como el resto y aterriza sin 403 de módulo
  admin por el solo hecho de ser SA

---

### Requirement: Seed Super Admin + ACME
El sistema SHALL, en seed de trabajo, crear (idempotente) un usuario Super Admin
(credenciales desde config/env) vinculado a una company de nombre ACME con NIF
`00000000A`, lista para desarrollo local.

#### Scenario: Seed idempotente ACME
- **WHEN** se ejecutan los seeders de la aplicación
- **THEN** existe company ACME (NIF 00000000A) y el Super Admin de config está vinculado
  a ella

---

### Requirement: Menú tenant según capas (salvo excepciones de shell)
El sistema SHALL seguir construyendo el resto del menú (no Dashboard, no Configuración SA)
según módulos Active + autorizados + encendidos y permisos del usuario (Basic/Optional),
alineado al kernel. SHALL agrupar/etiquetar de forma coherente con cuenta / empresa donde
haya funcionalidades existentes (p. ej. company-roles); sin inventar enlaces a maestros
aún no implementados.

#### Scenario: Tenant ve ítems de módulos elegibles
- **WHEN** un manager tiene workspace (u otro Basic/Optional) autorizado y encendido y
  permiso viewAny
- **THEN** esos ítems aparecen en el menú (además de Dashboard)


## MODIFIED Requirements

### Requirement: (menu-visibility / R-AUT-08 menú + EnsureModuleIsActive)
Aclarar excepciones de shell: Dashboard siempre; Configuración AdminOnly siempre para
Super Admin sin filtro de activación por company; middleware `module:` no 403 a Super
Admin en AdminOnly. Post-login SA alineado al flujo común. 10 escribe el delta sobre
`menu-visibility` / specs de módulo existentes.
