# Tasks

## 1. Extraer MediaPicker

- [x] 1.1 Crear `Form/MediaPicker` (open/onOpenChange/onSelect; shape
  id/name/alt/url; debounce + infinite scroll + AbortController sobre
  `admin.gallery.picker`); export en `Form/index`; verificar TypeScript /
  import limpio
- [x] 1.2 Cablear `Form/Wysiwyg` (`enableGalleryInsert`) a MediaPicker;
  eliminar `GalleryInsertDialog`; verificar assert fuente (Wysiwyg
  referencia MediaPicker; no queda GalleryInsertDialog en uso) y tests
  Content/Wysiwyg existentes adaptados en verde

## 2. Docs y DoD

- [x] 2.1 Glosario + fila catálogo `frontend.md` + ficha Admin (Galería
  Fase 3 / MediaPicker; Contents sigue único consumidor); V-01..V-20 no;
  E2E N/A
- [x] 2.2 Pint/ESLint según tocado + tests Feature picker + Content
  afectados en verde

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
- Segundo consumidor = change futuro (fuera de alcance A).
