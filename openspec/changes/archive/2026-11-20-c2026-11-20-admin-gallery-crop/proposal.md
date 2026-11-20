# Proposal

## Why

La Galería Fase 1 ya permite subir y reutilizar URLs, pero falta recortar o
redimensionar sin tocar el original. Los actores necesitan una copia editable
(estilo WP light) guardada como nuevo `MediaAsset`.

## What Changes

- UI en Index `/admin/gallery`: acción por card → modal crop/resize → confirma
  y crea **nuevo** `MediaAsset` (fichero nuevo, URL propia).
- Original **intacto** (fichero y metadatos).
- Copia **hereda** `name` y `alt` del original.
- Sin `parent_id` / linaje.
- Crop en **cliente** con librería React adecuada (`react-easy-crop`); alta en
  `libraries.md` en el mismo PR.
- Endpoint/Action de “copia desde origen” con perímetro + `gallery.create`;
  `company_id` null en este flujo (igual Fase 1).
- Tests Feature del happy path y de no mutar el original.
- Actualizar ficha Admin (crop = 1b hecho).

**Fuera:** Wysiwyg/picker, thumbs multi, upload empresa, sobrescribir original,
vídeo server.

## Capabilities

### New Capabilities

_(ninguna — amplía la capability existente)_

### Modified Capabilities

- `admin-gallery`: Index gana acción crop; nuevo requisito de copia por
  recorte/redimensión con herencia name/alt.

## Impact

- Front: modal crop en `Admin/Gallery/Index` (+ deps npm).
- Backend: Action + FormRequest + ruta; reutiliza storage/limites gallery.
- `docs/standards/libraries.md`, `docs/modules/Admin.md`.
- E2E Pest browser: N/A (Feature HTTP + assert front).
