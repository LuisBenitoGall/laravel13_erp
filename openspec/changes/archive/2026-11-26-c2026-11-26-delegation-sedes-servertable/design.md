# Design

## Context

See proposal.md — Why. Brief:
`docs/prompts/c2026-11-26-delegation-sedes-servertable.md`. Pivote actual
`workplace_delegation` solo tiene `is_preferred` (preferida **por sede**).
UI: `MatrixWorkplacesLinkedPanel` + props `linked_workplaces` en
`DelegationController@edit`. Index Workplaces ya usa ServerTable
(name/cp/town/featured).

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** ServerTable + columnas geo/fecha; columna/acción sede preferida
por Delegación (B2); docs glosario/Admin; tests.

**Non-Goals:** B1; cambiar kernel/docs comerciales; CRUD vínculo en
Delegación; `Workplace.featured`; mostrar `is_preferred` en esta tabla.

## Decisions

### D1 — Columna pivote `is_preferred_seat`
**Elección:** Añadir `workplace_delegation.is_preferred_seat` (bool default
false). Semántica: esta fila marca la sede como preferida **de la
Delegación** (`company_id`). ≤1 `true` por `company_id` (enforce en Action).
Cero preferidas = válido. Índice `(company_id, is_preferred_seat)`.
**Conservar** `is_preferred` sin renombrar ni migrar semántica.
**Descartado:** reutilizar `is_preferred` (B1); columna en `companies`;
usar `featured` de Workplace.

### D2 — API
**Elección:**
- `GET admin/delegations/{delegation}/workplaces/table` — ServerTable
  (perímetro hija de matriz; join town→province→country; campos name, cp,
  town_name, province_name, country_name, created_at, is_preferred_seat).
- `PUT admin/delegations/{delegation}/preferred-workplace` — body
  `workplace_id: int|null` (null = quitar preferida). Action
  `SetDelegationPreferredWorkplace`: valida vínculo pivote; limpia otras
  `is_preferred_seat` de esa Delegación; no toca `is_preferred`.

Authz: `updateDelegation` + perímetro. Sin Functionality nueva.
**Descartado:** toggle solo client-side; meter preferida en sync del mapa
Workplace (eje distinto).

### D3 — UI Edit Delegación
**Elección:** Tab Centros: `<ServerTable>` endpoint anterior; columnas
acordadas; row action Edit → `/admin/workplaces/{id}/edit`; toggle (o
acción) «Sede preferida» → PUT preferred-workplace (al marcar una, la
anterior se apaga; al desmarcar la única → null). Empty state + copy de
ayuda hacia Centros (`sus_centros_ayuda`); sin CTA botón (confirmado
producto). Quitar lista HTML y prop `linked_workplaces` del edit
Inertia (salvo que haga falta para empty hint — preferir table vacía).
i18n: `sede_preferida` (no reusar `delegacion_preferida`).
**Descartado:** Tabulator solo cliente con array Inertia; columna
`is_preferred`.

### D4 — Docs
Glosario: distinguir «Delegación preferida (por sede)» vs «Sede preferida
(por Delegación)». Admin.md: Edit Delegación ServerTable + B2. Kernel: sin
cambio (señalar en Notes de tasks si copy UI puede confundir).

### D5 — Vinculaciones
| V | ¿Sí? | Nota |
|---|---|---|
| V-01..V-06 | No | |
| V-07 | No | Sin permisos nuevos |
| V-08 | No | |
| V-09..V-17 | No | |
| V-18 | No | |
| V-19 | No | |
| V-20 | Opcional | Activity en set preferred seat si el patrón del mapa lo tiene |
| E2E | N/A | |

### D6 — Tests
Feature: table 200 + columnas; ≤1 seat; null OK; perímetro 403; cambiar
seat no muta `is_preferred`; CreateDelegation/SyncWorkplaceDelegations no
rompen (default `is_preferred_seat=false`).

## Risks / Trade-offs

- [Confusión de labels] → Mitigation: i18n distinto + HelpTip corto en tab.
- [Datos legacy todos `is_preferred=true`] → Mitigation: no migrar ese eje;
  la nueva columna empieza en false (usuario elige sede preferida).

## Migration Plan

1. Add `is_preferred_seat`. 2. Action + routes. 3. ServerTable UI. 4. Docs.
5. Tests. Rollback: drop column + revert UI a lista (no deseado).

## Open Questions

Ninguna bloqueante (cero preferidas = válido, espejo de preferida-por-sede).
