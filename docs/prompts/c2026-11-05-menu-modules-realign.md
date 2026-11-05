<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-05-menu-modules-realign
Rule: 10-architecture
Summary: Remodelar el menú para que todas las raíces salgan del catálogo `modules`
  (PDF MENU_ERP como mapa canónico). Sembrar estructura mínima; fusionar
  `admin`/`workspace`; quitar pilares hardcode; permiso Module AND Functionality;
  cache eficiente del catálogo. Sin pintar el menú completo del PDF.
Goals:
- **Fuente única**: cada raíz de menú = fila `modules`; cada subítem = Functionality.
  Cero `dashboardItem` / `empresasBlock` / `configurationBlock` /
  `CONFIGURATION_SLUGS` / `FUNCTIONALITY_GROUPS` sintéticos.
- Sembrar Modules mínimos (PDF leyenda): `dashboard` (1/Basic), `cuenta` (1),
  `usuarios` (1), `empresas` (1), `configuracion` (3/AdminOnly).
- **Fusionar**: Module `admin` → `configuracion` (reasignar Functionalities de
  catálogo); `workspace` → repartir y retirar (`company-users` → `usuarios`;
  `companies` → `empresas`; `company-modules` → `cuenta` como «Mis módulos»;
  `company-roles` → 10 ubica sin inventar ítem PDF — p. ej. `usuarios` o dejar
  documentado).
- Dashboard (y Manual futuro): **sin hijos**; ítem menú **clicable** al href
  (`/dashboard`). Requiere permiso comodín del Module.
- Rutas: prefijo `/admin/...` = zona ERP autenticada (como v1); URLs planas
  (`/admin/modules`, `/admin/roles`); **no** anidar por Module en path.
- Middleware `module:{slug}`: actualizar `module:admin` → `module:configuracion`
  (y equivalentes tras el reparto).
- **Permisos (cambio de kernel)**: modelo **AND** — hace falta `{module}.access`
  **y** el permiso de la Functionality (`viewAny`/acción) para usar esa
  funcionalidad. Sin comodín de módulo no se accede a ninguna de sus funcs.
  Actualizar `docs/standards/kernel.md` (+ MenuBuilder / policies / tests) en el
  mismo change. Super Admin bypass Spatie intacto; perímetro company intacto.
- Tres capas SaaS (catálogo / elegibilidad / activación) **sin cambio** de reglas
  Basic/Optional/AdminOnly.
- Carga menú eficiente: catálogo Modules+Functionalities en **Cache** Laravel
  (invalidar en Actions CUD Module/Functionality); por request filtrar company
  (Active∩autorizado∩encendido) + permisos AND. No JSON en disco como fuente.
- Reubicar solo Functionalities **ya existentes**; no crear pantallas nuevas
  (Mi cuenta → enlazar `profile` si aplica; Mis módulos → `company-modules`
  existente; polish tiles v1 = change posterior).
- Documentar mapa PDF como referencia de trabajo ordenado (1 change ≥ Module;
  ≥1 por Functionality al abrirla); no sembrar Optional vacíos del PDF.
NonGoals:
- Sembrar RRHH, Catálogo, Stocks, … ni subítems futuros del PDF.
- Pantalla «Todos» (directorio usuarios públicos).
- Rediseño tiles de company-modules al estilo captura v1.
- Landings corporativas / marketing fuera del ERP.
- Cambiar orden de menú con campo sort nuevo (dejar criterio actual tras
  remodelación).
Skills:
- openspec-propose
- tenancy-rbac (Module/Functionality, tres capas, menú, kernel AND)
Notes:
- Origen: `/opsx-explore`; PDF `MENU_ERP` (leyenda 1/2/3).
- Respuestas Luis: estructura mínima; fusión admin/workspace; `/admin` plano;
  cache eficiente (decisión explore: Cache Laravel); Mis módulos = capa 2;
  change estructural; Dashboard clicable; permisos **A = AND**.
- Kernel hoy documenta OR (comodín OR viewAny) y prohíbe AND v1 — este change
  **corrige el estándar** a AND (Luis). Señalado; no improvisar a medias.
- `company-module-authorizations` no está en PDF Configuración; 10 decide si
  queda bajo `configuracion` (herramienta SA) o fuera de menú.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Raíces de menú solo desde Modules
El sistema SHALL construir las raíces del menú exclusivamente desde filas
`modules` (más Functionalities hijas). SHALL NOT inventar raíces hardcode
(dashboard/empresas/configuracion sintéticos). Dashboard SHALL ser Module sin
Functionalities hijas y aparecer como ítem clicable a su href.

#### Scenario: Dashboard desde catálogo
- **WHEN** existe Module `dashboard` Active y el usuario tiene `dashboard.access`
  (y capas company si aplican)
- **THEN** el menú muestra Dashboard como ítem clicable sin submenú

#### Scenario: Sin raíz fantasma
- **WHEN** se genera el menú
- **THEN** no existen entradas cuya raíz no corresponda a un Module del catálogo

### Requirement: Estructura mínima sembrada
El sistema SHALL sembrar (o migrar) Modules `dashboard`, `cuenta`, `usuarios`,
`empresas`, `configuracion` con levels Basic/AdminOnly según PDF, y reasignar
Functionalities existentes a esos Modules. Modules `admin` y `workspace` SHALL
dejar de existir como slugs de catálogo tras la migración.

#### Scenario: Configuración SA
- **WHEN** un Super Admin con permisos adecuados obtiene el menú
- **THEN** ve el Module `configuracion` con las Functionalities de catálogo
  reubicadas (módulos, roles, permisos, países, monedas, …)

### Requirement: Permiso Module AND Functionality
Para acceder a una Functionality el usuario SHALL tener el comodín
`{module}.access` del Module padre **y** el permiso de acción de esa
Functionality. SHALL NOT bastar solo el `viewAny` de la Functionality sin el
comodín del Module. El estándar kernel SHALL reflejar esta regla AND.

#### Scenario: Solo viewAny de Functionality
- **WHEN** un usuario tiene `countries.viewAny` pero no `configuracion.access`
- **THEN** no ve Países en el menú y recibe 403 al forzar la URL

#### Scenario: Solo comodín de Module
- **WHEN** un usuario tiene `configuracion.access` pero no `countries.viewAny`
- **THEN** no ve ni usa Países (salvo Super Admin bypass)

### Requirement: Cache de catálogo de menú
El sistema SHALL cachear el árbol Modules/Functionalities Active y invalidarlo
al mutar Module o Functionality. El filtrado por company y permisos SHALL
aplicarse por request sobre ese catálogo cacheado.


## MODIFIED Requirements

### Requirement: Pilares de menú desde catálogo (sustituye síntesis)
Los pilares Empresas / Usuarios / Configuración / Dashboard SHALL proceder de
Modules del catálogo (no bloques hardcode). Specs `menu-core-pillars` /
`menu-visibility` SHALL actualizarse al contrato AND + raíces reales.

#### Scenario: SA ve Configuración como Module
- **WHEN** un Super Admin obtiene el menú
- **THEN** Configuración es el Module `configuracion` con sus Functionalities,
  no un bloque sintético
