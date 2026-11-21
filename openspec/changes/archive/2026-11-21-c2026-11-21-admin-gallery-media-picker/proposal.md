# Proposal

## Why

El selector de Galería (búsqueda + infinite scroll + picker JSON) vive
acoplado como `GalleryInsertDialog` solo al Wysiwyg. Fase 3 del roadmap
exige un `<MediaPicker>` reutilizable para futuros consumidores, sin
cablear aún una segunda pantalla (alcance A).

## What Changes

- Extraer `Form/MediaPicker` (API controlada open/onOpenChange/onSelect;
  activo `{ id, name, alt, url }`) reutilizando
  `GET admin/gallery/picker` (q, page, per_page, `{assets,meta}`).
- Wysiwyg con `enableGalleryInsert` usa MediaPicker por dentro;
  inserción `<img src alt>` sin regresión.
- Eliminar / dejar de exportar el dialog acoplado
  (`GalleryInsertDialog`).
- Documentar en form-kit, frontend.md, glosario y ficha Admin (Fase 3).

## Capabilities

### New Capabilities

_(ninguna — vive bajo form-kit)_

### Modified Capabilities

- `form-kit`: ADDED MediaPicker; Wysiwyg consume MediaPicker.

## Impact

- Front: `MediaPicker.tsx`, `Wysiwyg.tsx`, `Form/index`, tests de fuente.
- Docs: glossary, frontend.md, Admin.md.
- Backend: sin cambio de contrato salvo lo mínimo (preferible cero).
- E2E: N/A.
