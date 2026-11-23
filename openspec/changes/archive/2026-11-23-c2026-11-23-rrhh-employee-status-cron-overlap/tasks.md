# Tasks

## 1. Sync de estados (cron)

- [x] 1.1 Action HR `SyncEmployeeStatuses` (sin CurrentCompany): (1) caducar
  active con end_date < hoy; (2) activar scheduled con start ≤ hoy solo si no
  hay active del mismo user+company; verificar con Carbon::setTestNow
- [x] 1.2 Command `hr:sync-employee-statuses` + `Schedule::daily` en
  `routes/console.php`; verificar `artisan hr:sync-employee-statuses` y
  registro en schedule list
- [x] 1.3 Tests Pest: scheduled→active; active→ended; indefinido intacto;
  scheduled bloqueado si sigue active — verde

## 2. No solape y renovar indefinido

- [x] 2.1 Validación compartida (Request y/o Action) start > active.end_date
  misma company; i18n error; verificar rechazo ≤ end y OK día siguiente
- [x] 2.2 CreateEmployee / RenewEmployee: al cerrar active indefinido, forzar
  end_date = D−1; no conservar null; verificar renew indefinido
- [x] 2.3 Test solape cross-company permitido — verde

## 3. Docs y cierre

- [x] 3.1 Actualizar `docs/modules/HR.md` V-16 = Sí (cron sync estados)
- [x] 3.2 Pint dirty + tests del change en verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
