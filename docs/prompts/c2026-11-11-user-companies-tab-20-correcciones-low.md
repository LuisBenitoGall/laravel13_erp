Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-11-user-companies-tab
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **LOW** (no hay CRITICAL/HIGH pendientes). No reabran diseño.
Contrato: `openspec/changes/c2026-11-11-user-companies-tab/`
(design Decision 4 ServerTable; specs listado con columna nombre empresa).
Si este prompt y los artefactos chocan, mandan design + specs.
No reescribas proposal.md, design.md ni deltas de specs.
No archives. No E2E. No amplíes alcance (edit position/department, Empleo,
alta de vínculos, unificar DetachCompanyUser).

## Contexto

El HIGH del modal edit ya está cerrado. Listado, detach, sesión y perímetro
OK (`UserCompaniesMembershipTest` 6/6).

Observación LOW accionable: la búsqueda global del ServerTable solo mira
`position` y `department` (`UserCompanyController::membershipTable` →
`searchable(['position', 'department'])`). La columna principal es el
**nombre de empresa**; buscar «Alpha» no encuentra filas.

**Fuera de este prompt (no “arreglar”):**

- Risk LOW «logout self tras última company» → **aceptado en design**; no
  cambiar.
- MEDIUM duplicación `DetachCompanyUser` / `DetachUserCompany` → otro
  change/refactor.

## Qué hacer (LOW)

### 1. Incluir nombre de empresa en `searchable`

En `membershipTable` (profile y company-users usan el mismo helper):

1. `join` (o `leftJoin` si preferís, pero membership siempre tiene company)
   de `companies` sobre `user_companies.company_id`.
2. Asegurar `select` de `user_companies.*` (evitar ambigüedad de `id` /
   columnas al join).
3. Ampliar searchable a incluir el nombre, p. ej.
   `['companies.name', 'position', 'department']` (o el cualificado que
   use el schema).
4. Mantener `with('company')` para el Resource si sigue haciendo falta.

`TableBuilder` solo hace `orWhere($column, 'like', …)` — **no** soporta
`company.name` con whereHas. No extiendas TableBuilder en este fix salvo
que sea imprescindible; el join + columna cualificada es el camino mínimo.

No hace falta añadir sort/filter de columna `company_name` al whitelist
salvo que ya rompa algo; el LOW es la **búsqueda global** (`filter` field
`search`).

### 2. Evidencia Feature

En `UserCompaniesMembershipTest`, self (o company-users) con ≥2
memberships de nombres distintos (p. ej. Alpha / Beta ya en beforeEach):

- `GET …/companies/table` con el filtro search del contrato de tablas
  (mismo shape que usan otros Feature de ServerTable en el repo) buscando
  un fragmento del nombre de **una** company.
- Assert: `total` / data coherente (encuentra esa fila; no obliga a
  devolver las otras).

Mantener verdes detach / sesión / perímetro / «sin update».

### 3. Verificación

```text
php artisan test --compact tests/Feature/Admin/UserCompaniesMembershipTest.php
```

Pint paths PHP dirty (`vendor/bin/pint --dirty --format agent`).

## Fuera de alcance

- Modal/edición position/department (prohibido).
- Logout self / criterio menor id.
- Unificar Actions de detach.
- Archive / 30 formal salvo que Luis lo pida después.

## Gate de salida

- [x] `searchable` incluye nombre de empresa (vía join + columna
      cualificada).
- [x] Feature: search por nombre de company encuentra la fila.
- [x] Suite `UserCompaniesMembershipTest` verde; sin reintroducir update.
- [x] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
