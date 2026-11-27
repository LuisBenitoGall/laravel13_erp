# Proposal

## Why

RRHH necesita calendarios laborales por centro de trabajo (festivos/jornada
locales distintos por sede). El núcleo debe nacer como dominio transversal
`Calendar` (no solo dentro de HR) para reutilizar el shell en CRM/producción
más adelante, sin FullCalendar ni sync externo en esta oleada.

## What Changes

- Nuevo dominio PHP `App\Domains\Calendar` + ficha `docs/modules/Calendar.md`
  y actualización de `module-map` / glosario / ficha HR.
- Catálogo **DayType** (tipos de jornada) por Company (`BelongsToCompany`) +
  CRUD bajo Module `rrhh`.
- **WorkCalendar** 1:1 por Workplace (`workplace_id` unique) con `company_id`
  de la matriz (perímetro R-TEN); días/eventos con fecha, nombre y
  `day_type_id`.
- UI rejilla anual reutilizable (`YearCalendarView`) + selector de sede;
  heredar año→año; copiar entre sedes del mismo perímetro.
- Functionalities `day-types` y `calendars` bajo Module `rrhh`; menú
  Calendarios.
- Tests Pest (aislamiento, 1:1, heredar/copiar, permisos).

## Capabilities

### New Capabilities

- `calendar-day-types`: catálogo tipos de jornada por Company.
- `work-calendars`: calendario laboral 1:1 Workplace, eventos/días, rejilla
  anual, heredar/copiar.

### Modified Capabilities

_(ninguna spec main existente de calendarios; module-map/HR se actualizan por
tasks de registro, no como delta OpenSpec de capability previa.)_

## Impact

- Nuevo dominio Calendar (lee Admin: Company, Workplace).
- Menú/permisos Module `rrhh`; sin deps npm FullCalendar.
- E2E §4: N/A.
- `hours` v1 y sync/share: fuera de alcance.
