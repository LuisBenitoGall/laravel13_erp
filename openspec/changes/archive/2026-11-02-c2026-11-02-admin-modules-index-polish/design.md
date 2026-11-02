# Design

## Context

See `proposal.md` — Why. Estado actual relevante:

- `Admin/Modules/Index.tsx`: columnas name/color/icon/level/created_at/**status** +
  Acciones; icono y nivel sin formatter de celda; `shellCritical` oculta el toggle en
  `admin`/`workspace`.
- `UpdateModule` rechaza Inactive si `Module::isShellCritical()`; test
  `ModuleTableReferenceTest` lo aserta.
- Iconos de menú: `resolveMenuIcon` en `resources/js/lib/menuIcons.ts` (mapa parcial +
  fallback `LayoutGrid`).
- Ficha `docs/modules/Admin.md` ya existe; este change **no** crea módulo nuevo.
  Vinculaciones V-01..V-20: **no aplican** (polish UI + regla de status en catálogo
  existente).
- E2E (`e2e.md` §4): **no aplica** — no toca los flujos críticos listados.

## Goals / Non-Goals

**Goals:**

- Alinear Index Modules con el contrato delta (icono, nivel, sin Estado, toggle
  solo en nivel `optional`).
- Quitar el bloqueo backend de desactivación shell para que el toggle funcione de
  punta a punta.
- Actualizar tests feature que codifican la prohibición antigua.

**Non-Goals:**

- No rediseñar `ServerTable` ni el protocolo `/table`.
- No ampliar el mapa Lucide más allá de lo necesario para iconos ya usados en
  módulos sembrados (fallback existente OK).
- No inventar UX de “módulo en pruebas solo para admins” distinta del Inactive de
  catálogo + capas SaaS ya existentes.
- No pulir otros Index.
- No cambiar standards globales.

## Decisions

### D1 — Quitar columna Estado; filtro de status sale con ella
**Elección:** eliminar la columna `status` del Index. El toggle es la única señal
visual de activo/inactivo en el listado.
**Alternativa descartada:** mantener Estado + toggle (redundante; Luis lo rechazó).

### D2 — Toggle solo en módulos optativos
**Elección:** en `rowActions`, declarar `kind: 'toggle'` solo cuando
`row.level === 'optional'`. En `basic` y `admin_only` la fila ofrece únicamente Edit.
**Alternativa descartada:** toggle en todas las filas, incluidos shell (decisión
anterior de este change; Luis la sustituye). El guard por slug `shellCritical` no
vuelve: el criterio es el nivel.

### D3 — Backend: permitir Inactive en shell
**Elección:** eliminar el `ValidationException` por `isShellCritical()` en
`UpdateModule`. Si `isShellCritical` / `SHELL_SLUGS` quedan sin callers, eliminarlos
en el mismo change (evitar API muerta). Retirar o dejar de usar
`modulo_shell_no_desactivable` si ya no hay referencias.
**Alternativa descartada:** solo UI toggle + backend sigue bloqueando (rompería el
escenario acordado).
**Nota kernel:** capa 1 del menú (`modules.status = Active`) implica que Inactive en
`admin`/`workspace` puede dejar sin shell a quien dependa de `EnsureModuleIsActive`.
Es intencional (mantenimiento / pruebas); no se añade confirmación extra salvo que
Luis lo pida después — **fuera de este change**.

### D4 — Icono vía `resolveMenuIcon` + formatter Tabulator
**Elección:** formatter de celda que resuelve Lucide con `resolveMenuIcon`, monta el
SVG/nodo y pone `title` = nombre almacenado (trimmed). Reuso del helper del aside;
no nuevo componente de catálogo.
**Alternativa descartada:** mapa de iconos duplicado solo en Index.

### D5 — Nivel: formatter / valores de celda con las mismas keys i18n del filtro
**Elección:** mostrar en celda las traducciones ya usadas en `headerFilterValues`
(`nivel_admin_only`, `nivel_basic`, `nivel_optional`).
**Alternativa descartada:** traducir solo en filtro y dejar celda cruda.

### D6 — Ficha / module-map / E2E
**Elección:** no tocar ficha Admin salvo mención breve opcional en Notes de tasks;
`module-map` sin cambios; E2E §4 no aplica (declarado aquí).

## Risks / Trade-offs

- [Riesgo] Desactivar `admin` o `workspace` desde Edit o la API puede 403 el shell. →
  Mitigación: el listado no ofrece toggle en `basic` ni `admin_only`. El catálogo de
  módulos sigue fuera de `module:admin` para poder reactivar desde Edit si el listado
  sigue accesible, o vía BD/tinker en emergencia.
- [Riesgo] `persistKey` `admin.modules` puede recordar columna `status` oculta en
  localStorage. → Mitigación: tras quitar la columna del def, Tabulator ignora fields
  desconocidos; si molesta en QA, bump del `persistKey` (p. ej. `admin.modules.v2`) —
  20 decide solo si se observa bug.
- [Trade-off] Sin columna Estado no hay filtro select por status en cabecera, y las
  filas `basic` / `admin_only` tampoco muestran toggle. → Aceptado; el estado de esas
  filas se consulta en Edit.

## Migration Plan

1. Apply: front Index + `UpdateModule` + tests.
2. Sin migración BD.
3. Rollback: revertir commit del change (restaura guard y columna).

## Open Questions

Ninguna bloqueante. Confirmación de confirm-dialog extra al desactivar shell: **no**
en este change (no debatido).
