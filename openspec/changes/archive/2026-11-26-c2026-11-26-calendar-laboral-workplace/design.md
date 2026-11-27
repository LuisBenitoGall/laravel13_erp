# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-26-calendar-laboral-workplace.md`. v1: `DayTypes`
(company_id, name, color, is_working, …); `WorkCalendar` filas por
workplace+date+day_type (+hours). Livewire `Calendar` — no portar.
Module-map lista `WorkCalendar` bajo HR sin dominio Calendar.
E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Dominio Calendar; DayType; WorkCalendar 1:1; días con nombre+tipo;
rejilla anual wrapper; heredar/copiar; menú rrhh; docs/tests.

**Non-Goals:** FullCalendar; sync Google/Apple; share; calendarios no
laborales; WorkUserCalendar; hours v1; import festivos oficiales; deps npm
nuevas.

## Decisions

### D1 — Dominio `Calendar` (capa Extensiones)
**Elección:** `App\Domains\Calendar`. Capa 4 junto a HR/Projects. Lee Admin
(Workplace, Company). HR **no** posee los modelos; Module `rrhh` expone menú
y permisos. module-map: fila Calendar + HR → Calendar = D (consumo menú/UI).
**Descartado:** modelos solo en `App\Domains\HR`; dominio en Admin.

### D2 — Naming
| UI | Inglés | Tabla |
|---|---|---|
| Tipo de jornada | `DayType` | `day_types` |
| Calendario laboral | `WorkCalendar` | `work_calendars` |
| Día/evento sede | `WorkCalendarDay` | `work_calendar_days` |

Functionality slugs: `day-types`, `calendars`. Label menú **Calendarios**.
Wrapper UI: `YearCalendarView` (recursos/js; dominio-agnóstico props).

### D3 — DayType por Company
**Elección:** `BelongsToCompany`; soft delete; campos MVP: `name`, `color`
(string CSS/hex), `is_working` (bool). Sin portar cumulative/rank/modifiable
/user_enabled de v1. CRUD ServerTable + Create/Edit (modal o página corta).
**Descartado:** tipos globales; tipos por Workplace.

### D4 — WorkCalendar 1:1 Workplace + company_id
**Elección:**
- `workplace_id` unique NOT NULL (dueño de negocio).
- `company_id` = matriz dueña del workplace (`BelongsToCompany` / R-TEN-02);
  al crear, copiar de `workplace.company_id`; validar coherencia.
- Find-or-create al elegir sede en UI.
- Días: `work_calendar_id`, `date` (unique por calendario), `name`,
  `day_type_id` (exists day_types same company_id). Sin `hours` (NonGoal).

**Descartado:** solo workplace_id sin company_id (rompe R-TEN-02); N
calendarios por sede/año.

### D5 — Heredar año / copiar sedes
**Heredar N→N+1 (mismo WorkCalendar):** para cada día de año N, crear/upsert
en N+1 con misma fecha relativa (mes-día); 29-feb → omitir si N+1 no bisiesto.
Política: **overwrite** días existentes en destino para esas fechas.
**Copiar W1→W2 (año Y):** requiere W2 con WorkCalendar (find-or-create);
day_types mismos (misma company); overwrite fechas del año Y en destino.
Ambas Actions transaccionales + authz `calendars.update`.

### D6 — UI
- Rutas bajo prefijo `hr` existente (`routes/domains/hr.php`, names
  `hr.calendars.*` / `hr.day-types.*`; middleware `module:rrhh`).
- Hub Calendarios: selector Workplace (matriz sesión) → rejilla anual del
  WorkCalendar; CTA tipos de jornada; heredar; copiar a otra sede.
- `YearCalendarView`: 12 meses, celdas coloreadas por DayType; click →
  modal nombre+tipo (crear/editar/borrar día).
- Sin FullCalendar; sin npm nuevo.

### D7 — Authz / menú
- `day-types.*`, `calendars.*` bajo Module `rrhh` (Basic/Optional según HR
  actual — calcar `employees`).
- Menú: Calendarios (+ ítem Tipos de jornada o acceso desde hub — **ítem
  menú Calendarios** + enlace interno a tipos para no saturar; si playbook
  pide Functionality visible: ambos en menú RRHH).
**Elección menú:** dos entradas bajo RRHH — **Calendarios** y **Tipos de
jornada**.

### D8 — Vinculaciones V-01..V-20
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí | Permisos day-types.*, calendars.* |
| V-08 | Sí | Menú RRHH |
| V-09 | No | Module rrhh ya existe |
| V-10..V-17 | No | |
| V-18 | No | |
| V-19 | No | |
| V-20 | Sí | Auditable DayType / WorkCalendarDay |
| E2E | N/A | |

### D9 — `hours` v1
**Elección:** NonGoal este change (brief: no debatido). Revisar en oleada
horarios/fichajes.

## Risks / Trade-offs

- [Overwrite en heredar/copiar] → Mitigation: confirm UI; documentado.
- [Delegación vs matriz] → Workplaces son de matriz; DayTypes de CurrentCompany
  (matriz). OK con R-TEN-11.
- [Shell “transversal” sin FullCalendar] → YearCalendarView props tipados
  (days, onDayClick); suficiente para oleada 1.

## Migration Plan

1. Docs (glosario, module-map, fichas). 2. Migraciones DayType / WorkCalendar /
WorkCalendarDay. 3. Backend + policies + menú. 4. UI. 5. Tests.
Rollback: drop tablas + revert menú.

## Open Questions

Ninguna bloqueante.
