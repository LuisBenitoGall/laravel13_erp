# Tasks

## 1. Backend picker paginado

- [x] 1.1 Actualizar `MediaAssetController@picker` con `q` / `page` /
  `per_page` (default 24, max 48), filtro name|alt, `paginate`, respuesta
  `{ assets, meta }`; verificar Feature: página 1 con `q` filtra y
  incluye meta; sin `q` pagina sin devolver todos forzosamente
- [x] 1.2 Mantener 403 sin `gallery.view` y aislamiento company B
  (R-TEN-05) con el nuevo shape; verificar Features existentes adaptados
  en verde

## 2. Frontend GalleryInsertDialog

- [x] 2.1 Buscador + debounce ~300 ms + fetch page 1 replace +
  AbortController; verificar comportamiento (manual o assert fuente /
  test si aplica) y i18n es+en del placeholder/vacío búsqueda
- [x] 2.2 Infinite scroll (IntersectionObserver en sentinel del grid del
  modal): append page+1 si `current_page < last_page`; al cambiar `q`
  sustituir lista; verificar no concatena filtros distintos

## 3. Registro / DoD

- [x] 3.1 V-01..V-20: todos no; anotar Fase 2b en ficha Admin (Galería);
  E2E N/A; verificar ficha coherente
- [x] 3.2 Pint dirty + PHPStan + tests afectados del change en verde;
  verificar comandos OK

## Workflow follow-up

- Revisión Luis → `/opsx:apply` (20) → 25 → 30 `/opsx:archive`.
- Fase 3 MediaPicker sigue fuera.
