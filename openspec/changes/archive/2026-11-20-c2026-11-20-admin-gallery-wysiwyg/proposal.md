# Proposal

## Why

Los Articles de Contenidos usan Wysiwyg sin imágenes. La Galería ya ofrece
URLs reutilizables, pero no hay forma de insertarlas en el cuerpo. Además el
sanitizer HTML actual elimina cualquier `<img>` al guardar.

## What Changes

- Toolbar Wysiwyg (opt-in): botón «Galería» → modal embebido → inserta
  `<img src="…" alt="…">` desde un MediaAsset (solo Article).
- `alt` = alt del activo (vacío si no hay).
- TipTap Image (`@tiptap/extension-image`) + entrada en `libraries.md`.
- Ampliar `AllowedHtmlSanitizer` para `<img>` seguro (`src` + `alt`).
- Endpoint JSON ligero de activos visibles para el modal (mismo perímetro
  que Index Galería / `gallery.view`).
- **No** MediaPicker genérico (Fase 3); no Help; no URL remota libre.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `form-kit`: Wysiwyg con inserción opt-in desde galería + soporte Image.
- `contents-edit`: Article puede insertar/persistir img desde galería.
- `admin-gallery`: listado JSON para el selector del modal (si no se reutiliza
  otra vía ya existente).

## Impact

- Front: `Wysiwyg.tsx`, modal selector, `ContentForm` (solo Article).
- Backend: sanitizer; ruta picker; tests Feature Article + unit sanitizer.
- `libraries.md`, ficha Admin / contents.
- E2E: N/A (Feature + assert fuente).
