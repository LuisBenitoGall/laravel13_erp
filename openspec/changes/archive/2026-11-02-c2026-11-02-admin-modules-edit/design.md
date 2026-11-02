# Design

## Context

See `proposal.md` — Why. Piezas actuales:

- Create ya entregó LangTabs / IconPicker / ColorPicker / Switch (`Components/Form`).
- `UpdateModule` ya hace `unset($data['slug'])`; `UpdateModuleRequest` aún exige
  `level` y no valida name unique CI.
- `ViewActionBar` solo usa `resolveBackHref` (session origin); sin override fijo.
- Seed + `MenuBuilder::CONFIGURATION_SLUGS` incluyen `functionalities` → menú genérico.
- E2E §4: **N/A**. Ficha Admin existe; V-* entidad nueva: **N/A**.

## Goals / Non-Goals

**Goals:**

- Edit con tabs de página; form tab Módulo alineado al kit Create (sin level/slug).
- `backHref` fijo en shell para Modules/Edit → Index.
- Quitar Funcionalidades del menú Configuración.

**Non-Goals:**

- CRUD Functionalities anidado; Create Module; Wysiwyg; rediseñar navOrigin global
  salvo el override `backHref`.

## Decisions

### D1 — Tabs de página con primitiva UI existente
**Elección:** tabs Módulo | Funcionalidades con `ui/tabs` (o patrón Tabs ya en repo),
no esperar `Records/Tabs` diferidos. Tab Funcionalidades = empty state i18n
(“próximamente” / sin acciones CRUD).
**Alternativa descartada:** dos rutas distintas `/edit` vs `/functionalities`.

### D2 — Form Edit dedicado (no compartir Create con level)
**Elección:** página/form Edit propio (o props `mode: 'edit'`) **sin** Select level ni
slug; name + kit. Create conserva level.
**Alternativa descartada:** un solo ModuleForm con demasiados `if`.

### D3 — Update: rechazar level/slug en request
**Elección:** `UpdateModuleRequest` sin `level`/`slug`; `UpdateModule` sigue
unsetting slug; si llega `level` en payload, ignorar o rechazar (preferir no estar en
rules = ignored by validated()). Name unique CI `Rule::unique(...)->ignore($module)`.
Traducciones: misma regla Create (principal required).

### D4 — Volver: `backHref` opcional en AppLayout → ViewActionBar
**Elección:** prop `backHref?: string` en AppLayout/ViewActionBar; si está definida,
Volver usa esa URL (prioridad sobre `resolveBackHref`). Modules/Edit pasa
`backHref="/admin/modules"` y `showBack={true}`.
**Alternativa descartada:** solo documentar “entrar siempre desde Index” (frágil).

### D5 — Quitar menú Funcionalidades
**Elección:**
1. Quitar `functionalities` de `CONFIGURATION_SLUGS` y del mapa de rutas del
   MenuBuilder.
2. Seeder: no crear (o marcar Inactive) la Functionality `functionalities` en
   instalaciones nuevas; data migrate/test seed para entornos existentes.
3. Tests de menú (`MenuCorePillarsTest`, etc.) sin esperar ese slug.
4. Rutas/páginas Index Create Edit de Functionalities: **retirar del menú** en este
   change; eliminar o dejar rutas sin enlace (preferir retirar rutas públicas del
   web.php de functionalities Index/create/edit **o** devolver 410/redirect a modules
   — 20 elige el camino mínimo sin romper policies hasta el CRUD anidado; no dejar
   ítem de menú).

### D6 — Sin excepciones a standards
Actualizar tests/fixtures; no tocar kernel.

## Risks / Trade-offs

- [Riesgo] Deep links a `/admin/functionalities` tras quitar menú. → Mitigación: quitar
  o redirigir rutas; documentar en tasks.
- [Riesgo] Permisos Spatie `functionalities.*` huérfanos. → Mitigación: Functionality
  Inactive / no menú; limpieza de permisos opcional fuera de alcance salvo que rompa
  tests.
- [Trade-off] Tab Funcionalidades vacía hasta el siguiente change. → Aceptado (Luis).

## Migration Plan

1. Shell `backHref` → Edit UI → UpdateRequest/tests → menú/seeder/tests menú.
2. Sin migración de esquema BD.
3. Rollback: revertir change.

## Open Questions

Ninguna bloqueante.
