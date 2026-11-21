# Design

## Context

v1: `accounts` (name/slug/description/rate/duration/status) + CRUD
Configuración; vínculo real en `company_accounts` (fuera de alcance).
v2: Module `cuenta` ≠ este catálogo; `AccountingAccount` ≠ Account;
vision sitúa Plan/Subscription en Platform — este change lo ancla en
**Admin / Configuración** como v1 (decisión de producto del brief).
Motivación: proposal.md — Why.

## Goals / Non-Goals

**Goals:**

- Catálogo Account + Functionality `accounts` + CRUD ServerTable.
- Una sola default; máscara de renovación.
- Naming/glosario claros frente a `cuenta` / AccountingAccount.

**Non-Goals:**

- `company_accounts`, job caducidad, 403+CTA real, histórico, `guardian`,
  Cashier/Stripe.

## Decisions

### D1 — Naming y menú

- **Decisión:** modelo/tabla `Account` / `accounts`; Functionality slug
  `accounts`; UI «Cuentas»; ruta Index `/admin/accounts`. Menú bajo
  Module `configuracion` (tras `gallery` o junto a catálogos; orden en
  `RealignMenuCatalog`). Permisos `accounts.view|viewAny|create|update|delete`.
- **Por qué:** brief; evita chocar con Module `cuenta` y con
  AccountingAccount.
- **Glosario:** fila «Cuenta (SaaS / plantilla)» → `Account`.

### D2 — Schema Account

- **Decisión:**
  - `name` string required unique (no soft-deleted)
  - `slug` string unique (Str::slug from name on create/update name)
  - `description` text nullable
  - `rate` decimal(12,2) default 0 (coste plantilla; 2 decimales contables)
  - `duration` unsignedInteger nullable (días; null = sin duración fija /
    a resolver en company_accounts)
  - `status` string + enum `AccountStatus` Active/Inactive
  - `is_default` boolean default false
  - softDeletes + Auditable como Unit
- **Sin** `company_id` / BelongsToCompany (excepción R-TEN-01 global).
- **R-TEN-05:** N/A — mismo catálogo para todos los tenants; test de
  “visibilidad global” estilo Units.

### D3 — Default único

- **Decisión:** Action `SetDefaultAccount` (transaccional): pone
  `is_default=true` en el elegido y `false` en el resto. Seeder deja
  exactamente una default Active. Delete de la default: **prohibido**
  (422/negocio) hasta marcar otra; o reasignar en la misma Action — preferir
  **bloquear delete** de la default.
- Toggle Inactive de la default: permitido (aviso futuro); no auto-elige
  otra en este change.

### D4 — UI CRUD

- **Decisión:** calcar Unidades: Index ServerTable (name, rate, duration,
  default badge; Acciones toggle/Edit/Delete); Create/Edit **modales**;
  Active-first+name en `table` sin sorts cliente; Index sin columna Estado
  textual si hay toggle. Campo default: Switch en form + indicador en
  listado.

### D5 — Máscara renovación

- **Decisión:** ruta GET `/admin/accounts/renewal` (name
  `admin.accounts.renewal`), middleware módulo `configuracion` +
  `accounts.view` (o create — preferir **view**). Página Inertia
  `Admin/Accounts/RenewalComingSoon` (mismo patrón visual que
  `Account/ComingSoon`: copy i18n «próximamente»). **Sin** menú propio
  obligatorio (accesible por URL; el CTA 403 futuro apuntará aquí).
- **Por qué:** brief “solo la máscara”; no inventar formularios de
  renovación.

### D6 — Seeder

- **Decisión:** `AccountsSeeder` idempotente: al menos 1 Account Active
  `is_default=true` (datos mínimos v2; IDs no tienen que calcar v1 salvo
  que 20 encuentre dump útil). Documentar en README seeders. No auto-seed
  en Index GET. Opcional en DatabaseSeeder local.

### D7 — Futuro company_accounts (solo nota)

- Enum previsto (change siguiente): `CompanyAccountStatus`
  Pending / Active / Expired / Cancelled.
- Invariante futura: ≤1 Active por company; alta Company usa Account
  default; caducidad → 403 + CTA a `/admin/accounts/renewal`.
- **No** migrar tabla en este change.

### D8 — Vinculaciones / E2E

| V | ¿Sí? |
|---|---|
| V-07 permisos | Sí |
| V-08 menú | Sí |
| V-09 módulo nuevo | No (`configuracion`) |
| V-12 traducciones | No (name string como Unit; description plain) |
| V-15 migración v1 masiva | No (seeder mínimo) |
| V-16 cron | No (job caducidad = siguiente) |
| V-18 onboarding | Parcial doc: CreateCompany usará default **después** |
| V-20 audit | Sí Auditable como Unit |
| V-21 rutas módulo | Sí |

E2E Pest browser: **N/A** (catálogo modal; Feature basta).

### D9 — Excepciones a standards

- Catálogo global sin `company_id` (como Unit) — ya tipificado en
  architecture/R-TEN-01.
- Account en Admin aunque vision mencione Platform Plan — excepción
  explícita de producto (paridad v1 Configuración).

## Risks / Trade-offs

- [Confusión nombre Module `cuenta`] → Mitigación: glosario + labels UI
  «Cuentas» / Functionality `accounts`.
- [Borrar default] → Mitigación: bloqueo delete.
- [Máscara sin menú] → Mitigación: URL estable para CTA futuro.

## Migration Plan

1. Migration + model + enum + factory.
2. Policy + seeder permissions + RealignMenuCatalog.
3. Controller CRUD + table + RenewalComingSoon.
4. Front pages + i18n.
5. Tests + ficha + glossary + module-map si aplica (Account en Admin).
6. Pint / PHPStan / tests.

Rollback: revert; dropear `accounts`.

## Open Questions

_(ninguna — brief cerrado)_
