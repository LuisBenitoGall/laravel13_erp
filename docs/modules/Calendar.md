# Módulo Calendar — tipos de jornada y calendarios laborales

> Ficha playbook §1. Change: `c2026-11-26-calendar-laboral-workplace`.
> Dominio PHP: `App\Domains\Calendar`. Menú/permisos vía Module SaaS **`rrhh`**
> (HR consume Calendar; no posee los modelos).

## Propósito y alcance

**Entra:**

- Catálogo **DayType** (`day_types`): tipos de jornada por Company (CRUD
  ServerTable + Create/Edit).
- **WorkCalendar** 1:1 por Workplace (`workplace_id` unique) con `company_id`
  = matriz dueña del workplace.
- **WorkCalendarDay**: fecha + nombre + `day_type_id` (misma company).
- UI rejilla anual (`YearCalendarView`) + heredar año + copiar entre sedes.
- Functionalities `day-types` y `calendars` bajo Module `rrhh`.

**No entra:**

- FullCalendar / sync Google-Apple / CalDAV.
- Calendarios no laborales (CRM, producción).
- `WorkUserCalendar` / fichajes / planning.
- Campo `hours` de v1; import festivos oficiales.
- Dependencias npm nuevas.

## Entidades

| Entidad | Tabla | Dominio | Notas |
|---|---|---|---|
| `DayType` | `day_types` | Calendar | `BelongsToCompany`; soft delete; name, color, `is_working` |
| `WorkCalendar` | `work_calendars` | Calendar | 1:1 `workplace_id`; `company_id` matriz; BelongsToCompany |
| `WorkCalendarDay` | `work_calendar_days` | Calendar | unique (calendar, date); FK DayType misma company |

`Workplace` / `Company` se **leen** desde Admin.

## Dependencias

- **Admin** (Calendar → Admin = D): Company sesión, Workplace.
- **HR → Calendar = D**: menú RRHH y pantallas bajo `/rrhh/*` consumen el dominio;
  HR no escribe los modelos como dueño de dominio.

## Librerías

Ninguna fuera del canon (`docs/standards/libraries.md`). Sin FullCalendar.

## Permisos y roles

| Functionality slug | Permisos Spatie | Roles seed (matriz) |
|---|---|---|
| `day-types` | `day-types.{view,viewAny,create,update,delete}` + `rrhh.access` | manager: CRUD; guest: view |
| `calendars` | `calendars.{view,viewAny,create,update,delete}` + `rrhh.access` | manager: CRUD; guest: view |

Module level: **Optional** (`rrhh` ya existente).

## Referencia v1

| v1 | v2 |
|---|---|
| `DayTypes` / company | `DayType` / `day_types` (MVP: name, color, is_working) |
| `WorkCalendar` filas workplace+date+type(+hours) | `WorkCalendar` 1:1 + `WorkCalendarDay` (sin hours) |
| Livewire `Calendar` | Inertia + `YearCalendarView` |

## Migración de datos v1→v2

**No aplica** en este change (sin import masivo).

## Checklist de vinculaciones (§6)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | Sí | `day-types.*` / `calendars.*` + `rrhh.access` |
| V-08 | Sí | Menú RRHH: Calendarios + Tipos de jornada |
| V-09 | No | Module `rrhh` ya existe |
| V-10..V-19 | No | |
| V-20 | Sí | Auditable (`created_by`/`updated_by`) DayType / WorkCalendar / WorkCalendarDay |
| V-21 | Sí | Rutas `/rrhh/day-types*` y `/rrhh/calendars*` bajo `module:rrhh` |
| R-TEN-05 | Sí | DayType y WorkCalendar con `company_id` |
| E2E | N/A | Feature HTTP basta |
