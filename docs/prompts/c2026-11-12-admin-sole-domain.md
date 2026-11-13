<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-12-admin-sole-domain
Rule: 10-architecture
Summary: Admin pasa a ser el único dominio PHP del ERP funcional y el
  prefijo URL `/admin` el paraguas de todas las Functionalities. Absorber
  lo existente (Partners/Company, Banks, company-roles, profile) bajo
  Admin + `/admin/...`, con corte limpio. Sencillez: un dominio, un
  prefijo, un middleware.
Goals:
- Norma: un solo dominio `Admin` para el entorno funcional del ERP; no
  varios `Domains/*` de negocio.
- Toda Functionality bajo URL `/admin/...` (Mis Empresas =
  `/admin/companies`, no `/partners/companies`; también company-roles y
  profile).
- Absorber en este change lo existente: Partners (Company), Banks,
  rutas `/company/*`, `/profile` → código y rutas en Admin/`/admin`.
- Prefijo `/admin` = bloqueo/acceso uniforme con un solo middleware de
  grupo; sin rutas “especiales” por dominio.
- Corte limpio (sin redirects): el proyecto arranca; no hay backlinks.
- Actualizar specs y standards que digan Partners/Banks como dominios
  separados o rutas `/partners` / `/company`.
- Invariante de negocio que sí importa: siempre claro qué company actúa
  (`CurrentCompany`) y en qué rol (cliente | proveedor) cuando aplique.
NonGoals:
- Inventar módulos/entidades de negocio nuevas (obras, facturas, stock…).
- Cambiar onboarding / select / switch / login (shell fuera de `/admin`).
- Dashboard fuera de este rediseño de dominio (shell; ya acordado fuera
  del paraguas de Functionalities).
- Redirects legacy desde `/partners` o `/company`.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- testing-best-practices
Notes:
- Origen: `/opsx-explore`; Luis eligió **A** (Admin = único dominio).
- Motivo de sencillez: varios dominios no aportan valor frente a un solo
  Admin + `/admin`; v1 opera así sin problemas de facturas/stock por
  “fronteras de dominio”.
- Estado: `routes/domains/partners.php` prefix `partners`;
  `company.roles` bajo `/company`; profile bajo `/profile`; Banks ya
  URL `/admin` pero código en `Domains/Banks`; MenuBuilder →
  `partners.companies.*`.
- Corregir `module-map` / fichas Partners·Banks en el mismo change (el
  estándar multi-dominio queda obsoleto a propósito).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Admin único dominio funcional
El ERP funcional SHALL residir en un único dominio PHP `Admin`. SHALL NOT
mantener dominios PHP separados (`Partners`, `Banks`, etc.) para el
código funcional existente absorbido en este change.

#### Scenario: Código Company y Banks en Admin
- **WHEN** se revisa el árbol tras el change
- **THEN** Company (ex-Partners) y Banks viven bajo `App\Domains\Admin`
  (o estructura equivalente dentro de Admin), no en dominios propios

### Requirement: Prefijo URL /admin para Functionalities
Toda Functionality del ERP SHALL exponerse bajo el prefijo URL `/admin`.
Mis Empresas SHALL ser `/admin/companies` (no `/partners/companies`).
Company-roles y la ficha profile SHALL estar también bajo `/admin/...`.
El grupo de rutas `/admin` SHALL permitir aplicar middleware de acceso de
forma uniforme.

#### Scenario: Mis Empresas
- **WHEN** un actor abre Mis Empresas
- **THEN** la URL es `/admin/companies` (u hojas bajo ese recurso)

#### Scenario: Sin prefijos partners/company
- **WHEN** se listan rutas de Functionalities
- **THEN** no quedan prefijos `/partners` ni `/company` para esas
  funcionalidades

### Requirement: Corte limpio de rutas legacy
El sistema SHALL NOT mantener redirects desde `/partners/*` o
`/company/*`. Corte limpio.

#### Scenario: Legacy
- **WHEN** se solicita una antigua URL `/partners/companies`
- **THEN** no hay redirect oficial a `/admin/companies` (404 u
  equivalente; no compat layer)


## MODIFIED Requirements

### Requirement: Specs y menú dejan de apuntar a partners/company
(Specs company-create/edit, menú, tenancy, fichas y standards que asuman
`partners.*`, dominio Partners o Banks separado.) SHALL actualizarse a
Admin + `/admin/...` y a la norma de dominio único.

#### Scenario: Menú Mis Empresas
- **WHEN** el menú construye el href de Mis empresas
- **THEN** apunta a la ruta `admin` de companies (no `partners.companies`)

#### Scenario: Standards
- **WHEN** se lee module-map / fichas de módulo tras el change
- **THEN** no presentan Partners ni Banks como dominios PHP separados del
  ERP funcional
