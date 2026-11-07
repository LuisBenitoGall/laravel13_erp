# Design

## Context

Brief: `docs/prompts/c2026-11-06-permission-view-dashboard.md`. Hoy menú y
Policy `viewAny()` usan Spatie `.viewAny` (Laravel). Producto Luis: dashboard =
`.view`; detalle = `.viewAny`. Change 2 (roles hardening) depende de este.

## Goals / Non-Goals

**Goals:** semántica en standards; MenuBuilder; mapeo Policy Spatie; Index
gates; specs/tests menú + Index.

**Non-Goals:** quitar copy tenant; quitar AND manager en company-roles Index;
Create plataforma sin permissions; materialización; renombrar métodos Policy
PHP ni filas Spatie en BD.

## Decisions

### D1 — Mapeo Policy ↔ Spatie (sin renombrar métodos PHP)

| Método Policy (Laravel) | Permiso Spatie (producto) | Uso |
|---|---|---|
| `viewAny()` | `{func}.view` | Index / dashboard / table |
| `view()` | `{func}.viewAny` | Show / detalle de un registro |
| `create/update/delete` | sin cambio de sufijo | igual |

Documentar en kernel con tabla explícita para evitar confusión con Laravel.

### D2 — Menú

`MenuBuilder`: `$user->can($slug.'.view')` en lugar de `.viewAny`. Module hoja:
sigue solo comodín.

### D3 — Standards en el mismo change

20 actualiza `kernel.md`, `architecture.md` (R-AUT menú AND),
`.cursor/rules/01-kernel.mdc` / espejos si citan `viewAny` para menú. 10 no
edita standards en propose; el contrato lo exige en tasks.

### D4 — Specs a tocar

- **ADDED** `permission-view-semantics` (requisitos del brief).
- **MODIFIED** `menu-visibility`: requisitos «Permiso Module AND Functionality»
  y «Visibilidad de menú corregida» (y escenarios) con `.view`.
- **MODIFIED** `menu-core-pillars`: escenarios menú `profile` /
  `company-modules` que usen `viewAny` para ver ítem → `.view`.
- **MODIFIED** specs Index que digan “sin viewAny → 403 en Index” → “sin
  `.view`”: al menos
  `admin-geography-catalog`, `admin-currency-catalog`, `admin-units-catalog`,
  `admin-vat-types-catalog`, `admin-accounting-account-types-catalog`,
  `banks-catalog`, `admin-permission-catalog`, `company-users-index`,
  `users-directory`, y cualquier otro Index Feature que falle al cambiar
  Policies (20 cierra la lista con grep).

No hace falta reescribir cada catálogo entero: solo el requisito/escenario de
authz del Index.

### D5 — Datos / roles existentes

Manager sync suele otorgar los 5 permisos → sin regresión típica. Roles custom
que solo tengan `.viewAny` perderán menú/Index hasta recibir `.view` —
intencional. Sin migración de pivote automática en este change (no pedida).

### D6 — CompanyRolePolicy

Solo invertir sufijos Spatie en `viewAny()`/`view()`. **No** quitar
`isCompanyManager` del Index aquí (change 2).

## Risks / Trade-offs

- [Confusión Laravel vs producto] → tabla en kernel + comentario en Policies.
- [Specs/tests olvidados] → task grep `viewAny` en Policies/tests/specs.
- [UI matrices de roles etiquetan “viewAny”] → las 5 acciones canónicas
  siguen existiendo; copy i18n de acciones no es alcance salvo que diga
  “listado”.

## Migration Plan

Código + docs + tests. Sin migration SQL. Rollback = revert.

## Open Questions

Ninguna (brief cerrado). Change 2 se pide al archivar/cerrar este.
