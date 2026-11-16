# Proposal

## Why

El ERP necesita textos de ayuda contextual (tooltips/anclas) editables y
multiidioma, como v1 `/admin/contents` tipo info, sin WordPress y sin el
runtime JSON en disco. Hoy no hay entidad ni menú de Contenidos en v2.

## What Changes

- Modelo `Content` (kind Help): ancla `code`, campos name/title/excerpt/
  content/referrer/video, `company_id` nullable (MVP solo null), status,
  soft delete; Spatie JSON (sin `serialize()`).
- CRUD Index/Create/Edit bajo Configuración (Functionality `contents`).
- Resolución por `code` vía cache de mapa por locale + componente
  `<HelpTip />`.
- Glosario + ficha Admin; ítem menú Configuración.
- **No** galería imágenes, **no** kinds Article/Page, **no** writers tenant.

## Capabilities

### New Capabilities

- `contents-help`: Contenidos Help (anclas globales), CRUD, resolución
  cacheada y consumo UI.

### Modified Capabilities

- `menu-core-pillars`: ítem Contenidos bajo Module `configuracion`.

## Impact

- Dominio Admin: modelo/migración/Actions/Policy/rutas `/admin/contents`.
- Front: pages Contents + `HelpTip`; shared/cache help map.
- Seed Functionality + permisos; docs `Admin.md`, `glossary.md`,
  `module-map.md`.
- Sin deps npm nuevas (Wysiwyg TipTap ya en canon).
