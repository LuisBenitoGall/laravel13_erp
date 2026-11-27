<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-26-calendar-laboral-workplace
Rule: 10-architecture
Summary: Arrancar el dominio transversal Calendar consumido por RRHH: catálogo
  de tipos de jornada por Company, calendarios laborales 1:1 por Workplace
  (selector sede + eventos con nombre y tipo), UI rejilla anual con cambio de
  año, heredar entre años y copiar entre sedes. Menú RRHH → Calendarios.
  Shell de vista anual preparado para otros tipos de calendario futuros;
  sin FullCalendar ni sync externo en este change.
Goals:
- Dominio PHP **`Calendar`** (`App\Domains\Calendar`); HR/Module `rrhh` lo
  consume (menú/permisos). Actualizar module-map (nueva dependencia
  HR → Calendar / capa).
- Catálogo **tipos de jornada** (day types) **por Company** (matriz de
  sesión / BelongsToCompany): CRUD usable; las sedes solo eligen tipos ya
  creados por la empresa (no crean tipos en el centro).
- **Calendario laboral** anclado a `workplace_id` (no a `company_id` como
  dueño de negocio): **uno por sede**; perímetro tenant vía workplace→matriz.
  Alta/selección: selector de workplace; contenedor 1:1.
- **Eventos/días** del calendario de la sede: nombre propio + tipo de
  jornada (FK al catálogo de la empresa) + fecha; la sede crea/edita sus
  eventos.
- UI **rejilla anual** (no FullCalendar): navegación por año; componente
  wrapper reutilizable (`CalendarView` o nombre que fije 10) listo para
  otros calendarios después.
- Acciones: **heredar entre años** (p. ej. año N → N+1) y **copiar
  calendario entre sedes** (mismo grupo/matriz).
- Functionality(ies) bajo Module `rrhh` (p. ej. `calendars` y, si 10 lo
  separa, tipos de jornada); menú **Calendarios**.
- Glosario + ficha módulo(s) + tests Pest (1:1 sede, tipos por company,
  eventos, heredar/copiar, R-TEN-05 / perímetro).
NonGoals:
- FullCalendar / vistas semana-día agenda “Google-like”.
- Sync Google / Apple / CalDAV.
- Compartir calendarios entre users; invitados; RSVP.
- Calendarios CRM / producción / entregas (solo dejar el shell/dominio
  preparado).
- Calendario laboral del empleado (v1 `WorkUserCalendar`); fichajes;
  planning.
- Import automático de festivos oficiales (país/provincia) — solo herencia
  año↔año y copia sede↔sede debatidas.
- Livewire (v1 Calendar); clonar UI Livewire.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- v1-reference
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Delegaciones (sin changes activos). Visión
  cita calendarios laborales en RRHH; module-map ya nombra `WorkCalendar`
  bajo HR — este change **reubica núcleo en dominio Calendar**.
- v1: `WorkCalendar` = filas `workplace_id + date + day_type_id + hours`;
  `DayTypes` por company. Portar idea, no Livewire.
- Decisiones Luis (2026-11-26): (1) solo laboral workplace + shell UI;
  (2) 1 calendario/sede + cambio de año; (3) heredar años + copiar sedes;
  (4) dominio Calendar consumido por HR; (5) menú bajo `rrhh`; tipos de
  jornada por **empresa**; eventos con **nombre** en la sede; UI **rejilla
  anual** (FullCalendar aplazado).
- Naming inglés normativo (Calendar, DayType, CalendarEvent, …) lo fija 10;
  label UI «Calendarios» / «Tipos de jornada».
- `hours` de v1: no debatido → 10 decide si entra o queda NonGoal.
- Alta en `libraries.md`: **no** FullCalendar este change; sin deps npm
  nuevas salvo que 10 demuestre necesidad mínima.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Dominio Calendar consumido por RRHH
El sistema SHALL introducir el dominio `Calendar` con el núcleo de
calendarios laborales. El Module SaaS `rrhh` SHALL exponer el menú
Calendarios hacia ese núcleo. SHALL NOT colocar el modelo núcleo solo
dentro de `App\Domains\HR`.

#### Scenario: Menú RRHH
- **WHEN** un usuario con acceso a `rrhh` y permiso de calendarios obtiene
  el menú
- **THEN** ve Calendarios y puede abrir la funcionalidad

### Requirement: Tipos de jornada por Company
El sistema SHALL persistir un catálogo de tipos de jornada por Company
(sesión/matriz). Los calendarios de sede SHALL usar solo tipos de esa
Company. SHALL NOT crear tipos de jornada anclados al Workplace.

#### Scenario: Sede usa tipos de la empresa
- **WHEN** un actor edita un día del calendario de una sede
- **THEN** solo puede elegir tipos de jornada de la Company dueña del
  perímetro (no tipos de otra company)

### Requirement: Un calendario laboral por Workplace
El sistema SHALL asociar como máximo un calendario laboral por Workplace.
El dueño de negocio SHALL ser `workplace_id`. SHALL permitir seleccionar
la sede al operar el calendario.

#### Scenario: Una sede un calendario
- **WHEN** ya existe calendario laboral para la sede W y se intenta crear
  otro para W
- **THEN** se rechaza o se reutiliza el existente (unique respetado)

### Requirement: Eventos de sede con nombre y tipo
Cada marca/día del calendario laboral SHALL tener fecha, nombre (propio de
la sede) y tipo de jornada del catálogo de la empresa. La sede SHALL poder
crear y editar sus eventos en la rejilla anual.

#### Scenario: Alta de evento en un día
- **WHEN** el actor asigna en la sede W un día con nombre y tipo festivo
  de la empresa
- **THEN** queda persistido en el calendario de W para esa fecha

### Requirement: Rejilla anual y cambio de año
La UI SHALL mostrar una rejilla anual (wrapper reutilizable) con navegación
por año. SHALL NOT exigir FullCalendar en este change.

#### Scenario: Cambiar de año
- **WHEN** el actor navega del año N al N+1 en el calendario de una sede
- **THEN** ve la rejilla del año N+1 de ese mismo calendario

### Requirement: Heredar entre años
El sistema SHALL permitir heredar/copiar la configuración de días de un año
a otro dentro del mismo calendario de sede.

#### Scenario: Heredar N → N+1
- **WHEN** el actor ejecuta heredar desde el año N hacia N+1 en la sede W
- **THEN** los días/eventos aplicables quedan reflejados en N+1 según la
  regla que fije design (sin borrar el eje de tipos de la empresa)

### Requirement: Copiar calendario entre sedes
El sistema SHALL permitir copiar el calendario laboral (días/eventos de un
año o el conjunto que fije design) desde una sede origen a una sede destino
del mismo perímetro/matriz.

#### Scenario: Copia entre sedes
- **WHEN** el actor copia desde sede W1 hacia W2
- **THEN** W2 recibe los eventos copiados usando tipos de jornada de la
  misma Company


## MODIFIED Requirements

### Requirement: Mapa de dominios / HR (module-map)
El mapa SHALL reconocer el dominio `Calendar` y que HR consume calendarios
laborales desde ese dominio (ya no solo entidad `WorkCalendar` listada bajo
HR sin dominio propio).

#### Scenario: Dependencia documentada
- **WHEN** se consulte module-map tras el change
- **THEN** aparece Calendar y la relación de consumo desde HR/rrhh
