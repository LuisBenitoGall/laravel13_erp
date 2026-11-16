<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-contents-create
Rule: 10-architecture
Summary: Primer change de Contenidos: modelo + Index + alta mínima
  (nombre + tipo), status Inactive por defecto, redirect a Edit
  placeholder, menú Configuración, e infraestructura HelpTip/cache.
  Edit real = change siguiente.
Goals:
- Entidad Content con kinds Información (Help) y Contenido; `code`
  ancla; `company_id` nullable (MVP escritura null = global); soft
  delete; status (default Inactive al crear).
- Create: solo `name` + tipo (sin status en UI); Continuar → Edit
  placeholder.
- Index ServerTable + Functionality/menú bajo Configuración (permisos;
  SA vía menú).
- Infra `<HelpTip code>` + mapa cache por locale (sin JSON runtime);
  tips solo útiles cuando haya textos Active (Edit = otro change).
NonGoals:
- Formulario de Edit real (campos por tipo, Wysiwyg, activar/publicar
  con UX completa) — **otro change**.
- Galería de imágenes.
- WordPress / CMS genérico; overrides Help por tenant.
- `published_at` / `published_end` (no decidido; no en este change).
- Sustituir o archivar a ciegas `c2026-11-16-contents-help` sin que 10
  decida qué hacer con artefactos huérfanos previos.
Skills:
- openspec-propose
- new-module
- v1-reference
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` remodelando Create (captura v1) tras propose
  `contents-help`. Decisiones Luis: Create = name+tipo; sin status en
  create → default Inactive; tipo determina Edit futuro; opción A =
  Create permite ambos tipos; Edit Contenido stub diferido a change
  Edit; post-create = placeholder Edit (B); HelpTip+cache sí en este
  change.
- Brief anterior `docs/prompts/c2026-11-16-contents-help.md` queda
  supersedido en alcance; 10 usa este brief como contrato Create.
- Multiidioma Spatie / columnas title|excerpt|content: preparar en
  modelo si hace falta para Edit siguiente; Create no los rellena.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Create Content mínimo
El sistema SHALL permitir crear un Content con solo nombre y tipo
(Información | Contenido). SHALL NOT mostrar status en el Create.
SHALL persistir status Inactive por defecto. SHALL generar `code`
único. Tras guardar SHALL redirigir al Edit placeholder.

#### Scenario: Alta Continuar
- **WHEN** el actor completa nombre y tipo y continúa
- **THEN** se crea el Content Inactive con company_id null y navega al
  Edit placeholder

### Requirement: Index y menú Contenidos
El sistema SHALL listar Contents en Index bajo Configuración
(Functionality + permisos Spatie).

#### Scenario: SA abre Contenidos
- **WHEN** Super Admin abre Contenidos desde Configuración
- **THEN** ve el Index

### Requirement: Edit placeholder
Tras el Create, el sistema SHALL mostrar una página Edit placeholder
(sin formulario de edición completo de este change).

#### Scenario: Placeholder
- **WHEN** el actor llega al Edit tras Continuar
- **THEN** ve la página placeholder (no el Edit real por tipo)

### Requirement: HelpTip e infraestructura de resolución
El sistema SHALL exponer `<HelpTip code>` y resolución por mapa cacheado
por locale (BD source of truth; sin JSON runtime). Codes sin contenido
Active usable SHALL no romper la página.

#### Scenario: Tip sin contenido útil
- **WHEN** se renderiza un code inexistente o Inactive
- **THEN** no hay error de página; el tip no muestra contenido útil


## MODIFIED Requirements

### Requirement: Menú pilares / Configuración
(El menú Configuración SA.) SHALL incluir la Functionality de Contenidos
cuando capas y permisos lo permitan.

#### Scenario: Entrada Contenidos
- **WHEN** un Super Admin con acceso abre Configuración
- **THEN** puede navegar a Contenidos
