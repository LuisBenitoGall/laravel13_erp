# Design

## Context

Brief: `docs/prompts/c2026-11-23-rrhh-employee-status-cron-overlap.md`.
Oleada 1 RRHH archivada. Hoy: `EmployeeStatus::initialFor` solo en create;
`RenewEmployee` / `CreateEmployee` cierran actives al crear otro `active` pero
no validan `start > end` del active con fin; no hay cron. Patrón scheduler:
`company-accounts:expire` (comando + Action + `Schedule::daily`).

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:**

- Sync diario de estados (activar / caducar) idempotente, company_id explícito.
- No solape vs `active` misma company; renovar indefinido → fin D−1.
- Tests + V-16 en ficha HR.

**Non-Goals:**

- Notificaciones de vencimiento próximo.
- Solape vs `scheduled`/`ended`.
- Cambios ContractType / UI de catálogo.

## Decisions

1. **Comando `hr:sync-employee-statuses`**  
   Action `SyncEmployeeStatuses` (o nombre equivalente) en dominio HR:
   - Sin `CurrentCompany`: `Employee::query()->withoutGlobalScope('company')`.
   - Orden: (1) caducar `active` con `end_date < hoy` → `ended`; (2) activar
     `scheduled` con `start_date ≤ hoy` **solo si** no queda otro `active` para
     el mismo `(user_id, company_id)`.  
   Si un `scheduled` está listo pero sigue habiendo un `active` indefinido (u
   otro active), **no** lo activa el cron (queda `scheduled` hasta renovar/
   finalizar a mano). No cerrar indefinidos desde el cron.  
   Alternativa descartada: cron que cierre indefinidos al activar scheduled —
   no debatido; solo renovación cierra indefinidos con D−1.

2. **Schedule**  
   `Schedule::command('hr:sync-employee-statuses')->daily();` en
   `routes/console.php`, mismo estilo que `company-accounts:expire`.

3. **No solape (opción A)**  
   En Create y Renew (y validación FormRequest): cargar el `active` del mismo
   `user_id` + `company_id` (sesión). Si existe y `end_date` no null y
   `nuevo.start_date <= end_date` → ValidationException / error i18n.  
   Si el active es indefinido (`end_date` null): Create que nace `active`
   sigue cerrando con D−1 (como renew); Create que nace `scheduled` no exige
   fin del indefinido (opción A solo cuando hay `end_date`). Renew siempre
   cierra indefinido con D−1.  
   Scope company: filtro `company_id` del periodo nuevo; no mirar otras
   companies.

4. **Renew indefinido**  
   Confirmar/ajustar `RenewEmployee`: `end_date` del anterior =
   `newStart->subDay()` (aunque hubiera quedado null); status `ended`. Luego
   Create del nuevo. Ya casi está; asegurar que no se conserve un null.

5. **Create que cierra actives**  
   Antes de `closeActivePeriods`, aplicar la regla de solape si el active
   tiene `end_date`. Tras validar, al cerrar indefinidos usar D−1 respecto al
   nuevo start (igual que renew).

6. **V-16**  
   Actualizar `docs/modules/HR.md`: V-16 Sí (cron sync estados).

7. **Tests**  
   Feature: comando Artisan (o Action) con fechas congeladas (`Carbon::setTestNow`);
   solape rechazado / día siguiente OK; solape cross-company OK; renew
   indefinido; scheduled no activa si sigue active indefinido.

## Risks / Trade-offs

- [Scheduled bloqueado tras indefinido] → Mitigation: documentado; usuario debe
  renovar/finalizar; UI ya tiene Renovar.
- [Varios scheduled el mismo día] → Mitigation: activar de a uno (p. ej. menor
  `start_date` luego `id`); el resto espera al siguiente ciclo si ya hay active.
- [Timezone] → Mitigation: comparar fechas a `startOfDay` en timezone app
  (igual que `initialFor`).

## Migration Plan

1. Action sync + Command + Schedule.  
2. Validación solape + ajuste Renew/Create.  
3. i18n error solape.  
4. Tests + ficha V-16. Sin migration BD.

## Open Questions

Ninguna bloqueante del brief.
