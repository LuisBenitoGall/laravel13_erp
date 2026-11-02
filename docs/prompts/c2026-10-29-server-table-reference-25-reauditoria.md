Usa el agente @.cursor/agents/agent-25-architecture-review.md guiado por
docs/agents/25-architecture-review.md y la rule `.cursor/rules/25-architecture-review.mdc`.

Change: c2026-10-29-server-table-reference
Origen: informe 30-testing (gate de archive bloqueado).
Motivo: el último veredicto formal de 25 fue **RETURN TO IMPLEMENTATION**. Desde
entonces se cerraron los HIGH (canal `filter[]` + contrato Acciones = toggle + Edit)
y 30 endureció el sort en la descarga Excel/PDF. 30 **no puede archivar** sin un
veredicto nuevo: APPROVED o APPROVED WITH OBSERVATIONS.

Contrato: `openspec/changes/c2026-10-29-server-table-reference/` (proposal, design,
deltas `specs/server-table-reference` y `specs/admin-ui-login`, `tasks.md`).
Si este prompt y los artefactos chocan, **mandan los artefactos**.

## Qué eres y qué no

- Auditoría **solo lectura**. No implementes. No toques código, specs, design,
  tasks ni docs. No archives. No escribas tests.
- Asume que la implementación es incorrecta hasta demostrar lo contrario.
- Salida única: informe completo con recomendación explícita.

## Alcance de esta re-auditoría (breve, no de cero)

No reabres el change entero como si fuera el primer apply. Contrastas el estado
**actual** contra el contrato **ya enmendado** (opción A: toggle se queda).

### 1. Cierre de los HIGH del RETURN TO IMPLEMENTATION previo

Verifica evidencia y cierra o reabre con severidad:

| Hallazgo previo | Qué debe estar ya |
|---|---|
| Canal `filter[]` del AdvancedSearchModal | `onApply(filters)` con `{field,type,value}`; `ServerTable` con `advancedFilters` + `advancedFilterMode` `merge`/`replace`; mismo `filter` en table y Excel/PDF; Modules **sin** modal ni CTA `busqueda_avanzada`. Test: `TableFiltersChannelTest`. |
| Acciones Modules «solo Edit» vs toggle | Contrato alineado: design D3, deltas Acciones = toggle + Edit (sin Delete), task 6.13 opción A. Index: `kind: 'toggle'` + Edit. Sin recarga (`patchJson`). Tamaño toggle alineado (`--st-action-size`, task 6.15). |

Si alguno sigue abierto → HIGH/CRITICAL y **RETURN TO IMPLEMENTATION** con evidencia.

### 2. Fix de 30-testing (sort en export)

30 reenvió el sort activo en la descarga Excel/PDF (`sortRef` en `ServerTable` +
tests en `ModuleTableReferenceTest` / `TableFiltersChannelTest`). Contrástalo con
design D5 y `frontend.md` («mismos filtros/sort»). Si cumple → no lo marques como
deuda abierta. Si falla → hallazgo con severidad.

### 3. Contrato vs código (checklist mínima)

Confirma sin inventar requisitos nuevos:

- Modules Index: columnas name, color, icon, level, created_at, status + Acciones;
  sin slug; sin AdvancedSearch; Acciones = toggle (excepto shell admin/workspace) + Edit.
- `TableRequest` export `xlsx|csv|pdf`; cap 5000; whitelist ModuleTableRequest.
- Norma `frontend.md` / templates alineada al delta.
- Tasks 1.1–6.15 marcadas con evidencia (no casillas vacías).

### 4. MEDIUM / LOW del informe anterior

Revisa si siguen abiertos (icono `rowActions`, enums level/status en UI, recrear
Tabulator al ocultar columnas, 302 en validación del PATCH status, etc.).

- Si no rompen el contrato → **APPROVED WITH OBSERVATIONS** (listarlos).
- Si alguno ahora rompe spec/design → subir severidad y valorar RETURN.

### 5. Fuera de tu alcance (no bloquees por esto)

- Suite Browser / Playwright en rojo: es hand-off a **Agent 40** (prompt
  `docs/prompts/c2026-10-29-server-table-reference-40-correcciones.md`). Tú no
  reparas el runner. Puedes anotar en Risks: «Browser rojo hasta 40».
- No pedirle a 20 que migre otros Index.
- No reabrir la opción B (quitar toggle): Luis eligió A; el contrato ya lo dice.

## Formato de salida (obligatorio)

Informe completo según la ficha 25:

1. Spec Compliance / Design Compliance / Layers / Domain / Maintainability
2. Hallazgos por severidad (CRITICAL / HIGH / MEDIUM / LOW) con evidencia
3. Technical Debt / Risks
4. **Recommendation** (una sola):
   - `APPROVED`
   - `APPROVED WITH OBSERVATIONS`
   - `RETURN TO IMPLEMENTATION`

Motivo en 2–4 frases. Hand-off explícito:

- Si APPROVED / APPROVED WITH OBSERVATIONS → «sigue Agent 40 (Browser) y luego 30 archive».
- Si RETURN → incidencias concretas para 20 (o 10 si es contrato), sin archive.

## Gate

- [ ] Artefactos leídos antes de juzgar el código.
- [ ] Diff/estado actual contrastado contra el contrato enmendado.
- [ ] Cada hallazgo con severidad y evidencia.
- [ ] Recomendación explícita.
- [ ] Cero archivos del repo modificados por esta revisión.
