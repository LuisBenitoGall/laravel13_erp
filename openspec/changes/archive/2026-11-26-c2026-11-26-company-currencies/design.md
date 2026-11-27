# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-26-company-currencies.md`. Existe `Currency` global y
`ExchangeRate` global; ninguna Company tiene monedas de uso. Patrón pivote
cercano: `SyncCompanySectors` + tab Sectores. Hub readiness hoy:
`CompanySetupReadiness` = nif + logo + ≥1 workplace Active (2 pasos).

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:**

- Pivote + Action sync + UI sección Info (Company/Delegación) + paso hub.
- Readiness 3 pasos; soft-gates intactos.
- Glosario / Admin.md / module-map; R-TEN-05.

**Non-Goals:**

- Tab Monedas; FX/snapshot docs; ExchangeRate en UI; herencia matriz→hija;
  Functionality/menú nuevos; columna Index ServerTable; muro ERP.

## Decisions

### D1 — Pivote `company_currencies`
**Elección:** Tabla `company_currencies` con `company_id`, `currency_id`,
`is_default` (bool), unique `(company_id, currency_id)`. ≤1 fila
`is_default=true` por company (enforce en Action, no unique parcial frágil).
`Company::currencies()` BelongsToMany + pivot; helper `defaultCurrency()`.
FK restrict a `currencies` / cascade o restrict a `companies` (mismo criterio
que `company_sector`).
**Descartado:** `companies.currency_id` solo (opción B); herencia (C); tabla
modelo Eloquent intermedia obligatoria (pivot basta).

### D2 — Sync independiente (no metido en UpdateCompany)
**Elección:** Action `SyncCompanyCurrencies` + FormRequest; rutas:
- `PUT admin.companies.currencies` → `/admin/companies/{company}/currencies`
- `PUT admin.delegations.currencies` → `/admin/delegations/{delegation}/currencies`
- `PUT admin.company-settings.currencies` → sesión CurrentCompany

Payload: `currency_ids: int[]`, `default_currency_id: int|null`.
- Set vacío → limpia pivote; `default_currency_id` null.
- Set no vacío → default required ∈ ids; exactamente una `is_default=true`.
- IDs nuevos: solo `Currency` Active no trashed. IDs ya vinculados pueden
  permanecer aunque la Currency haya pasado a Inactive (UI avisa).

Authz: `companies.update` + vínculo (matriz Edit); `delegations.update` +
hija de sesión (Delegación); hub = authz company-settings / update de la
company en sesión. **Sin** Functionality nueva.
**Descartado:** meter monedas en el mismo PUT de identidad (acopla saves);
Functionality `company-currencies.*` (overkill vs sectores N:N con menú
propio ausente pero permisos dedicados — aquí el brief no los pidió).

### D3 — UI: sección en Info + componente compartido
**Elección:** `CompanyCurrenciesPanel` (o nombre cercano) en tab Información
de Edit Company y Edit Delegación; mismo panel en hub paso Monedas. Select
default + checks permitidas; code/símbolo/nombre; HelpTip/copy i18n; aviso
si default/permitida Inactive. Guardado propio del panel (no tab).
RecordSummary: chip/código de default si existe (sí en este change).
**Descartado:** tab dedicado; columna Index (aplazado).

### D4 — Create sin moneda; onboarding sí
**Elección:** `CreateCompany` / `CreateDelegation` sin tocar pivote.
Readiness: `steps.currencies.done` = existe default; `total_steps` = 3;
`complete` = identity ∧ workplaces ∧ currencies. Banner/modal usan la
nueva readiness (companies “completas” previas → banner hasta configurar).
**Descartado:** seed EUR al crear; muro duro.

### D5 — Naming / docs
**Elección:** Inglés normativo pivote/relación «company currencies»; glosario
fila Monedas de uso / `company_currencies`. Actualizar Admin.md (Company,
Delegaciones, hub readiness) y module-map (Company + pivote).
**Descartado:** entidad `CompanyCurrency` de primer nivel con CRUD propio.

### D6 — Vinculaciones (V-01..V-20)
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | No | Sin permisos/Functionality nuevos |
| V-08 | No | Sin menú nuevo |
| V-09..V-17 | No | |
| V-18 | Sí | Paso + readiness company-settings |
| V-19 | No | |
| V-20 | Sí | Activity log en sync (atributos pivote / Company) |
| V-21 | N/A o sí | Tests feature del sync |

### D7 — Tests
Feature: sync default único; vacío; rechazo default ∉ set; Create sin
moneda; Edit matriz/delegación panel; readiness incompleta/completa;
R-TEN-05 (company A no ve/mutate monedas de B). Sin E2E browser.

## Risks / Trade-offs

- [Banner masivo tras deploy] → Mitigation: esperado; paso hub claro; sin muro.
- [Currency Inactive como default] → Mitigation: aviso UI; sync no exige
  re-Active si ya vinculada; selectores solo Active para altas.
- [Delegación sin onboarding] → Mitigation: solo Edit; readiness no aplica a
  hijas (sesión = matriz).

## Migration Plan

1. Migration pivote. 2. Model/Action/rutas. 3. UI Edit + hub + readiness.
4. Docs (glosario/Admin/module-map). 5. Tests. Rollback: drop pivote +
revert readiness (companies vuelven a 2 pasos).

## Open Questions

Ninguna bloqueante.
