# Proposal

## Why

Contenidos (y el resto del ERP) necesitan imágenes reutilizables por URL sin
resubir en cada uso. Hoy no hay mediática de plataforma bajo Configuración; el
logo de empresa y `UserImage` no cubren ese caso. Fase 1 entrega la librería
mínima (estilo WP light) para Super Admin / Configuración.

## What Changes

- Nueva entidad **`MediaAsset`** (`media_assets`): imágenes con `company_id`
  nullable, `name`, `alt`, path en disco `public`, soft delete.
- Functionality **`gallery`** bajo Module `configuracion`, menú **Galería**
  inmediatamente después de Contenidos; ruta Index `/admin/gallery`.
- UI Index: **grid 6 columnas** (excepción ServerTable), upload, editar name/alt,
  copiar URL, eliminar. Solo imágenes.
- Altas Fase 1: solo SA / flujo Configuración con `company_id` null. Upload con
  `company_id` de empresa **bloqueado** (modelo preparado).
- Visibilidad de `company_id` null: Super Admin o actores con permisos
  `gallery.*` (perímetro de no-null: companies vinculadas; R-TEN-05).
- Canon: activar `react-dropzone` en `libraries.md` para este módulo; config
  límites mime/tamaño.
- Ficha Admin + glosario.

**Fuera:** crop/copia (1b), Wysiwyg/picker (2–3), vídeo en servidor, otra URL
no-SA, multi-thumbs WP, `parent_id`, migrar `File_` v1.

## Capabilities

### New Capabilities

- `admin-gallery`: librería de `MediaAsset` bajo Configuración (CRUD Fase 1,
  tenancy nullable, grid, URL).

### Modified Capabilities

- `menu-core-pillars`: ítem Galería bajo `configuracion` (tras Contenidos).

## Impact

- Dominio Admin: model/migration/factory, Actions, Policy, FormRequests,
  Controller, routes, RealignMenuCatalog + MenuFunctionalityHref + i18n menú.
- Front: `Pages/Admin/Gallery/Index.tsx` (+ componente grid reutilizable futuro).
- Tests Feature (CRUD, authz, aislamiento company_id no-null).
- `docs/modules/Admin.md`, `glossary.md`, `libraries.md`.
- E2E Pest browser: N/A (declarado en design).
