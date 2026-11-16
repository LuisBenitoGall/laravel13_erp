# Proposal

## Why

Hace falta el primer eslabón de Contenidos: poder dar de alta piezas
(Información / Contenido) desde Configuración, con ancla `code` e
infraestructura de tips, sin el formulario de edición completo ni el
runtime JSON de v1. El propose `contents-help` quedó supersedido al
partir Create vs Edit.

## What Changes

- Modelo `Content` (kinds Help + Article), `code`, `company_id` null en
  alta, status default Inactive, soft delete; columnas Spatie
  title/excerpt/content preparadas (vacías en Create).
- Index ServerTable + Create mínimo (name + kind) → redirect Edit
  **placeholder**.
- Functionality `contents` + ítem menú Configuración.
- `<HelpTip />` + mapa cache por locale (sin JSON runtime).
- Artefactos huérfanos `contents-help` archivados como superseded;
  main `contents-help` + menú: reconciliar en archive (30).
- **No** Edit real, galería, published_*, writers tenant.

## Capabilities

### New Capabilities

- `contents-create`: alta/Index/placeholder Edit + HelpTip infra para
  Contents.

### Modified Capabilities

_(ninguna — ítem Contenidos ya está en `menu-core-pillars` main tras
sync prematuro de `contents-help`; este change implementa el ítem / no
re-ADDEA el requirement)_

## Impact

- Admin: migración/modelo/Actions Store + Index/Create/placeholder;
  Policy; rutas `/admin/contents`.
- Front: Contents pages + HelpTip; shared/cache help map.
- Docs: glossary, Admin.md, module-map (ajustar ficha Create).
- Change Edit futuro consumirá el mismo modelo.
