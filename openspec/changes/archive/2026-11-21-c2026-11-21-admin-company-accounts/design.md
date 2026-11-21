# Design

## Context

Catálogo `Account` + default listos. CreateCompany no asigna suscripción.
`/account` (Functionality `profile` bajo Module `cuenta`) es ComingSoon.
Máscara `/admin/accounts/renewal` sin lógica. Motivación: proposal — Why.
Decisiones de explore: alcance A; renovación desde Mi cuenta; actores =
quien tiene acceso a Mi cuenta; sin guardian.

## Goals / Non-Goals

**Goals:** `company_accounts`, ≤1 Active, default al alta, job caducidad,
403+CTA, histórico+renovación en Mi cuenta.

**Non-Goals:** guardian, Cashier/Stripe, rediseño catálogo Account.

## Decisions

### D1 — Modelo `CompanyAccount`

- Tabla `company_accounts`: `company_id` FK, `account_id` FK, `start_date`
  date, `end_date` date nullable, `price` decimal(12,2), `payment_date`
  date nullable, `status` string + enum, softDeletes, Auditable.
- Trait **`BelongsToCompany`** (dato de tenant; R-TEN-05 sí).
- Relación `account()` → Account global.
- **Sin** `guardian`.

### D2 — Enum `CompanyAccountStatus`

- `Pending` | `Active` | `Expired` | `Cancelled` (string column).
- Active = vigente para acceso.
- Expired = caducada por fecha/job.
- Cancelled = sustituida por renovación (o baja manual si se añade).
- Pending = reservado (alta futura / start_date > hoy); job puede
  activar Pending→Active cuando `start_date <= today` (paridad v1
  simplificada). **Mínimo este change:** CreateCompany crea **Active**
  directo; Pending opcional si start futura en renovación — preferir
  renovación siempre Active desde hoy.

### D3 — Invariante ≤1 Active

- Action `ActivateCompanyAccount` / `RenewCompanyAccount`: en
  transacción, la Active previa de esa company → `Cancelled` (si se
  renueva) o el job pone `Expired`; luego inserta/activa la nueva.
- Unique parcial no portable en MySQL fácil → enforcing en Action +
  test; índice compuesto `(company_id, status)` no unique.
- Intento de segunda Active fuera de Action → rechazo.

### D4 — Alta Company

- Tras crear Company, Action `AssignDefaultCompanyAccount`:
  - Requiere Account `is_default` Active; si no hay → fallo de negocio
    (no crear company a medias: misma transacción CreateCompany).
  - `start_date` = today; `end_date` = today+duration días si duration
    set, si null → end_date null (sin caducidad automática hasta que
    haya fecha).
  - `price` = rate del Account; `payment_date` null; status Active.

### D5 — Job caducidad

- Command `company-accounts:expire` diario (Schedule).
- Active con `end_date` **no null** y `end_date < today` → Expired.
- No toca end_date null (indefinida).
- V-16 sí.

### D6 — Gate de acceso (403 + CTA)

- Middleware `EnsureCompanyAccountIsActive` en el grupo `company`
  (después de EnsureCompanySelected).
- Si CurrentCompany no tiene CompanyAccount Active con vigencia
  (`end_date` null OR `end_date >= today`): respuesta **403 Inertia**
  (página dedicada o shared error) con mensaje i18n + CTA Link a
  `route('account.show')` (Mi cuenta / renovación).
- **Whitelist** (no gate): `account.show`, rutas de renovación bajo
  account, `companies.select` / switch, logout, onboarding company.
  Sin whitelist no se puede renovar.
- Super Admin: **mismo gate** para la company de sesión (brief: empresa
  y usuarios); puede renovar en Mi cuenta o cambiar de company.
- Detección también si status Expired aunque end_date futura (consistencia).

### D7 — Mi cuenta UI

- Sustituir `Account/ComingSoon` por página con:
  - Resumen cuenta Active actual (nombre plantilla, fechas, precio).
  - ServerTable histórico company_accounts de **CurrentCompany**
    (R-FE-04 en fechas/price).
  - CTA «Renovar» → modal/form: elige Account del catálogo Active (o
    default preseleccionado); confirma → `RenewCompanyAccount`.
- Authz: `profile.view` (+ module cuenta capas) como hoy el menú Mi
  cuenta. Renovar: mismo permiso `profile.view` (brief: quien accede a
  Mi cuenta actúa); no inventar Functionality nueva salvo que 20 vea
  choque — entonces `profile.update` para POST renew (preferir
  **profile.view** para GET y **profile.update** para mutación renew
  si el kit de permisos ya distingue; documentar en tasks: usar
  `profile.update` para POST renew si existe en seeder, si no ampliar).

### D8 — Máscara admin renewal

- `GET admin/accounts/renewal` → **redirect** `route('account.show')`
  (302). Eliminar página RenewalComingSoon o dejar de usarla.

### D9 — Specs tocados

- ADDED `admin-company-accounts`.
- MODIFIED `menu-core-pillars` requirement «Cuenta con Mi cuenta…»
  (ya no «próximamente»).

### D10 — Vinculaciones

| V | |
|---|---|
| V-07 | No permiso Functionality nueva si reutiliza profile.* |
| V-08 | No menú nuevo (misma entrada Mi cuenta) |
| V-16 | Sí job |
| V-18 | Sí CreateCompany |
| V-20 | Auditable CompanyAccount |
| V-21 | Rutas /account bajo module cuenta |
| R-TEN-05 | Sí CompanyAccount |

E2E: opcional rol 40 — Feature gate + renew prioritarios; E2E N/A si no
toca e2e.md §4 obligatorio.

### D11 — Excepciones

Ninguna a kernel; Account catalog global ya tipificado.

## Risks / Trade-offs

- [403 bloquea renovación] → Mitigación whitelist D6.
- [end_date null nunca caduca] → Aceptado; renovación/manual futuro.
- [profile.update vs view para renew] → Tasks fijan permiso mutación.
- [Empresas existentes sin company_accounts] → Seeder/comando one-shot
  AssignDefault para companies Active sin fila (task migración datos).

## Migration Plan

1. Migration + model + enum + factory.
2. Actions Assign/Renew + enganche CreateCompany.
3. Job + Schedule.
4. Middleware + página 403 + whitelist.
5. Mi cuenta UI + table + renew; redirect admin renewal.
6. Backfill companies existentes.
7. Tests + docs.

Rollback: quitar middleware primero; tabla queda.

## Open Questions

_(ninguna — brief cerrado; detalle profile.update vs view en tasks)_
