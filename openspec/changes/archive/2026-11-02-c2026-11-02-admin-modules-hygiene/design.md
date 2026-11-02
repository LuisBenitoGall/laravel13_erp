# Design

## Context

See `proposal.md` — Why. Piezas actuales:

- `DeleteModule` solo hace soft delete; sin guard shell; sin UI.
- Index Acciones: toggle solo `optional` + Edit; sin Delete.
- Edit: level no se muestra; ActionBar = Nueva funcionalidad (+ Volver).
- Rutas genéricas `functionalities` resource + table; páginas
  `Admin/Functionalities/*` aún en repo.
- `ModuleForm.tsx` sin callers de página; `ShellActionBarTest` aún lee ese fichero.
- E2E §4 / V-* nueva entidad: **N/A**.

## Goals / Non-Goals

**Goals:** soft delete UI + bloqueo shell; level readonly; retirar genéricos
Functionalities; borrar ModuleForm; tests alineados.

**Non-Goals:** mover botón Nueva; RHF+zod; RecordEditLayout; reabrir toggle universal;
purge Spatie.

## Decisions

### D1 — Shell no soft-deleteable
**Elección:** `DeleteModule` (y UI) rechazan `admin`/`workspace`. Mantenimiento =
Inactive (ya permitido).
**Alternativa descartada:** permitir soft delete shell (simétrico a Inactive) —
demasiado destructivo para el catálogo de configuración.

### D2 — Acciones Index
**Elección:** Delete último con `confirm` en filas no shell. Orden optional:
toggle → Edit → Delete; basic/admin_only no shell: Edit → Delete.

### D3 — Delete en Edit ActionBar
**Elección:** `type: 'delete'` en `actions` si no shell; onConfirm →
`router.delete` → Index. Mantener Nueva funcionalidad en ActionBar sin cambios.

### D4 — Level readonly
**Elección:** campo/texto i18n (`nivel_*`) junto a name/status; no Select. Prop
`level` debe llegar en ModuleResource del edit (si falta, añadir).

### D5 — Retirar Functionalities genéricas
**Elección:** eliminar páginas `Admin/Functionalities/{Index,Create,Edit}` + form
compartido genérico; quitar `Route::resource` except nested y
`functionalities.table` global; métodos `index/create/store/edit/table` genéricos del
controller si quedan sin ruta. Conservar `update`/`updateStatus`/`destroy`/
`moduleTable`/`moduleStore`.

### D6 — ModuleForm
**Elección:** borrar fichero; actualizar `ShellActionBarTest` para asertar
`Create.tsx` (actions vacío) en lugar de ModuleForm.

## Risks / Trade-offs

- [Riesgo] Tests o bookmarks a `/admin/functionalities`. → Mitigación: retirar rutas
  (404) o redirect a modules Index — preferir 404 limpio tras quitar resource.
- [Riesgo] Soft delete Module con Functionalities hijas. → SoftDeletes en Module no
  borra hijas; listados nested filtran module_id; OK. Restore fuera de alcance.
- [Trade-off] Shell sin Delete en UI puede confundir vs Inactive. → Documentar en
  empty tooltip/ausencia de botón.

## Migration Plan

1. Backend guard + tests delete → Index/Edit UI → level prop → retirar genéricos →
   borrar ModuleForm + tests shell.
2. Sin migración BD.
3. Rollback: revert change.

## Open Questions

Ninguna (A/B shell resuelto en D1).
