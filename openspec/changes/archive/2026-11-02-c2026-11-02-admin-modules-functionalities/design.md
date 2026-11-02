# Design

## Context

See `proposal.md` — Why. Estado actual:

- Module Edit ya tiene tabs; pestaña Funcionalidades = placeholder i18n.
- `CreateFunctionality` provisiona 5 permisos + `{module}.access`;
  `DeleteFunctionality` solo soft-delete (no toca Spatie) — alineado con Luis.
- `StoreFunctionalityRequest` aún exige `slug` del cliente; Update exige name/module_id
  (hay que restringir Edit modal a label).
- Rutas genéricas `admin/functionalities*` existen pero fuera de menú.
- E2E §4: **N/A**. V-* nueva entidad: **N/A** (Functionality ya en catálogo).

## Goals / Non-Goals

**Goals:** CRUD anidado completo en la pestaña; ServerTable; modales; slug auto;
toggle JSON; soft delete sin purge de permisos.

**Non-Goals:** Index/menú genérico; cambiar tab Módulo; borrar permisos Spatie;
Wysiwyg.

## Decisions

### D1 — Rutas nested bajo Module
**Elección:**
- `GET modules/{module}/functionalities/table`
- `POST modules/{module}/functionalities` (store; module_id del route)
- `PATCH functionalities/{functionality}/status` (o nested)
- `PUT/PATCH functionalities/{functionality}` (update label)
- `DELETE functionalities/{functionality}`

Reusar Actions existentes; controller dedicado nested o métodos en
`FunctionalityController` scoped. Respuestas de mutaciones desde modal: Inertia
`back()` / JSON que permita refrescar ServerTable sin salir de Edit (preferir
`router.reload` parcial o `patchJson` + invalidate table como Modules status).

**Alternativa descartada:** seguir usando solo resource genérico con `module_id` en
body sin nest (menos claro para el table filtrado).

### D2 — Store: slug server-side + name unique CI
**Elección:** espejo Modules Create — `prepareForValidation` / Action con
`Str::slug($name)`; unique CI en name y slug; status default Active si omitido.
Modal no muestra slug.

### D3 — Update modal: solo label
**Elección:** `UpdateFunctionalityRequest` para este flujo: únicamente `label.*`
(principal required). Unset name/module_id/slug/status en update desde modal.
Status solo vía toggle endpoint.

### D4 — UI: Dialog + LangTabs; ActionBar Nueva
**Elección:** shadcn Dialog para create/edit; LangTabs solo label; botón
`type: 'button'` en `actions` de AppLayout que abre create modal (estado React en
Edit). Tras éxito: cerrar modal + refrescar ServerTable.

### D5 — ServerTable embebido
**Elección:** `endpoint` al table nested; `persistKey` p. ej.
`admin.modules.{id}.functionalities`; columnas label/name; rowActions toggle/edit/delete.
Sin advanced search. Export: no requerido en este change (salvo que ServerTable lo
exija por defecto — entonces `exportable={false}`).

### D6 — Delete
**Elección:** confirmar vía rowAction delete de ServerTable; llamar
`DeleteFunctionality` (soft delete); **no** `Permission::where(...)->delete()`.

### D7 — Generic Index pages
**Elección:** no reactivar menú; rutas Index/Create/Edit genéricas pueden quedar
huérfanas o redirigir — fuera del foco salvo que rompan tests; 20 no invierte en
esas páginas.

## Risks / Trade-offs

- [Riesgo] Soft-deleted Functionality con mismo slug/name bloquea recreación. →
  Mitigación: unique rules respetan SoftDeletes (sin trashed) como Modules.
- [Riesgo] Permisos huérfanos tras delete. → Aceptado explícitamente por Luis.
- [Trade-off] Scenario name histórico «sin CRUD» en catalog se reinterpreta en MODIFIED
  (OpenSpec no permite dropear el nombre). → Contenido THEN actualizado.

## Migration Plan

1. Backend routes/requests/toggle → Front Edit tab + modales → tests.
2. Sin migración BD.
3. Rollback: revert change.

## Open Questions

Ninguna bloqueante.
