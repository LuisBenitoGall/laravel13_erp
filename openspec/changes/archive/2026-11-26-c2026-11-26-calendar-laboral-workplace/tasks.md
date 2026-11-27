# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Glosario: DayType, WorkCalendar, WorkCalendarDay (+ dominio Calendar)
- [x] 1.2 Crear `docs/modules/Calendar.md` (playbook §1; V-07/V-08/V-20 sí;
      E2E N/A; entidades; dependencias Admin)
- [x] 1.3 Actualizar `docs/standards/module-map.md`: dominio Calendar; HR
      consume Calendar; quitar WorkCalendar como solo-HR sin dominio
- [x] 1.4 Actualizar `docs/modules/HR.md`: calendarios consumen dominio
      Calendar (ya no NonGoal ciego)

## 2. Datos — DayType

- [x] 2.1 Migración `day_types` (company_id, name, color, is_working,
      softDeletes) + Model `DayType` BelongsToCompany + Factory
- [x] 2.2 Policy + FormRequests + Actions CRUD + Controller/routes bajo
      convención HR; permisos `day-types.*`; RealignMenu / seeder
      Functionality
- [x] 2.3 Index ServerTable + Create/Edit; i18n; feature R-TEN-05 + menú
      (V-07/V-08)

## 3. Datos — WorkCalendar / días

- [x] 3.1 Migraciones `work_calendars` (company_id, workplace_id unique) +
      `work_calendar_days` (work_calendar_id, date unique, name,
      day_type_id); Models + Factories
- [x] 3.2 Actions: FindOrCreateWorkCalendar; Upsert/Delete WorkCalendarDay;
      InheritWorkCalendarYear; CopyWorkCalendarYear (overwrite; 29-feb)
- [x] 3.3 Policy `calendars.*` + Controllers (show/year JSON o Inertia;
      inherit; copy) + rutas; menú Calendarios
- [x] 3.4 Feature Pest: 1:1 workplace; día con tipo misma company; rechazo
      tipo ajeno; heredar; copiar; perímetro R-TEN-05; activity log (V-20)

## 4. Frontend rejilla anual

- [x] 4.1 Componente `YearCalendarView` (props days/types/year/onDayClick;
      sin FullCalendar; sin npm nuevo)
- [x] 4.2 Página Calendarios: selector Workplace + año + rejilla + modal
      día (nombre, tipo) + CTAs heredar/copiar + enlace tipos
- [x] 4.3 i18n es/en/ca

## 5. Calidad

- [x] 5.1 Pint (`--dirty`) + PHPStan en PHP tocado; features del change en
      verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
