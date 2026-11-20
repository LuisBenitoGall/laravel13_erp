# Proposal

## Why

El selector de Galería del Wysiwyg (Fase 2) carga **todos** los MediaAsset
visibles en un solo JSON. Con pocas imágenes funciona; con muchas
ralentiza apertura (payload + miniaturas). Antes de extraer `<MediaPicker>`
(Fase 3) hay que endurecer el contrato del picker para no heredar
“cargar todos”.

## What Changes

- `GET admin/gallery/picker`: `q` (name/alt), `page` + `per_page` (tope),
  respuesta `{ assets, meta }` (`current_page`, `last_page`, `per_page`,
  `total`). Mismo perímetro/`gallery.view` que hoy.
- Modal Wysiwyg: buscador (debounce ~300 ms) + infinite scroll; al
  cambiar `q` → página 1 y sustituir lista; `AbortController` al cambiar
  `q` o cerrar.
- **No** MediaPicker, **no** Index Galería, **no** upload empresa.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `admin-gallery`: listado JSON del picker pasa a búsqueda + paginación.
- `form-kit`: selector embebido del Wysiwyg con buscador e infinite scroll.

## Impact

- Backend: `MediaAssetController@picker` (+ tests Feature existentes).
- Front: `GalleryInsertDialog` (+ i18n buscador/vacío).
- Ficha Admin (nota Fase 2b). E2E: N/A.
