<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-contents-help
Rule: 10-architecture
Summary: MVP de Contenidos tipo Help (anclas): CRUD bajo Configuración,
  textos globales multiidioma, resolución eficiente por `code` (sin JSON
  runtime), consumo vía componente de ancla en UI.
Goals:
- Entidad Content kind Help: `code` ancla, `company_id` nullable (MVP
  solo null = global, mismo texto para todos los tenants), soft delete,
  status; campos como v1: name, title, excerpt, content (rico), referrer;
  vídeo embebido por URL (sin galería de imágenes en este change).
- Multiidioma según locales activos (Spatie JSON; sin `serialize()`).
- Index/CRUD bajo menú Configuración; permisos Spatie (hoy Super Admin
  por el menú SA).
- Resolución runtime: mapa cacheado por locale invalidado al guardar
  (no `storage/json/contents.json` como runtime).
- Componente de consumo `<HelpTip code="…" />` (o equivalente); módulos
  futuros solo insertan el código del ancla.
NonGoals:
- Kind Article/Page/Slot / blog / landings tenant writers.
- Galería de imágenes (`ContentImage`) — otro change.
- WordPress / CMS genérico.
- Cache JSON en disco como fuente de lectura en request.
- Overrides de Help por tenant (texto distinto por company).
Skills:
- openspec-propose
- new-module
- v1-reference
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` + referencia v1 `/admin/contents` +
  `Helpers::info($code)` (popover) + `contents.code` único.
- Decisiones Luis: MVP solo anclas; menú Configuración; globales
  `company_id` null; columna nullable preparada; escritura por permisos
  (SA vía menú); title+excerpt+content como v1; soft delete; sin
  librería CMS; galería imágenes fuera de alcance.
- `code` inmutable tras create (acordado en cierre de modelo MVP).
- Fetch: BD source of truth + cache mapa `code → {title, excerpt,
  content?, referrer, video?}` por locale; front hidrata una vez /
  lookup O(1); no N+1 por icono.
- Ventana `published_at` / `published_end` de v1: no decidida
  explícitamente en explore; decide 10 (incluir o diferir en Help).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Playbook + ficha módulo / Functionality `contents` (o nombre glosario).


## ADDED Requirements

### Requirement: CRUD Content Help bajo Configuración
El sistema SHALL permitir listar, crear, editar y soft-delete Contents
de kind Help bajo Configuración, con permisos Spatie. Los Help de MVP
SHALL tener `company_id` null (globales). SHALL NOT usar `serialize()`.
Campos de redacción: name, title, excerpt, content multiidioma, referrer;
vídeo por URL si aplica.

#### Scenario: Alta Help global
- **WHEN** un actor con permiso crea un Content Help con code y textos
- **THEN** se persiste con `company_id` null y queda disponible por ancla

#### Scenario: Soft delete
- **WHEN** el actor elimina un Content Help
- **THEN** se aplica soft delete y deja de resolverse en anclas activas

### Requirement: Ancla code y consumo HelpTip
El sistema SHALL identificar cada Help por `code` único (inmutable tras
alta) y SHALL exponer un componente de UI que, dado un `code`, muestre
title/excerpt (popover/tip) según locale, sin romper si el code no existe.

#### Scenario: Resolver ancla
- **WHEN** una pantalla renderiza la ancla `expense-date` y existe el
  Content activo
- **THEN** el usuario ve el tip en su idioma

#### Scenario: Code inexistente
- **WHEN** se pide un code sin Content
- **THEN** no hay error de página; el tip no muestra contenido

### Requirement: Resolución eficiente sin JSON runtime
El sistema SHALL resolver Help desde BD (o cache derivado), invalidando
al guardar/borrar. SHALL NOT leer `storage/json/contents.json` en cada
request como fuente de verdad.

#### Scenario: Tras editar
- **WHEN** se actualiza un Help y se vuelve a mostrar una ancla
- **THEN** se ve el texto nuevo (cache invalidada o equivalente)

### Requirement: Mismo texto para todos los tenants
Los Help globales SHALL mostrar el mismo contenido con independencia de
la company de sesión.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company y abre una pantalla con ancla
- **THEN** el tip Help global es el mismo


## MODIFIED Requirements

### Requirement: Menú pilares / Configuración
(El menú Configuración SA.) SHALL incluir la Functionality de Contenidos
(Help) cuando el catálogo/activación/autorización y permisos lo permitan.

#### Scenario: Entrada Contenidos
- **WHEN** un Super Admin con acceso abre Configuración
- **THEN** puede navegar a Contenidos
