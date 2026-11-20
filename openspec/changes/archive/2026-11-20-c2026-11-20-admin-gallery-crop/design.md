# Design

## Context

Brief `docs/prompts/c2026-11-20-admin-gallery-crop.md`. Fase 1 archivada/
implementada: grid, multi-upload `images[]`, name/alt, URL, sin Volver,
`company_id` null en altas, sin `parent_id`.

E2E browser: **no aplica**.

## Goals / Non-Goals

**Goals:** modal crop → blob → nuevo MediaAsset; hereda name/alt; original OK.

**Non-Goals:** Wysiwyg/picker; parent_id; overwrite; upload empresa; Intervention
server-side crop.

## Decisions

### D1 — Librería: `react-easy-crop`

- **Elegido:** `react-easy-crop` (React 19, mantenimiento activo, canvas export
  documentado). Canon en `libraries.md` «por módulo / Galería crop».
- **Descartado:** cropperjs (más peso / DX distinto); crop solo CSS (no fichero).
- Flujo cliente: cargar imagen origen (url) → área crop + zoom → `canvas` →
  `Blob` (jpeg/png/webp según origen o jpeg por defecto) → `FormData` al server.

### D2 — API: copia desde origen

- **Ruta:** `POST /admin/gallery/{media_asset}/copies` (`admin.gallery.copies`).
- **Body:** `image` (fichero blob recortado), mismos límites mime/KB que
  `config/gallery.php` (un fichero por request).
- **Action** `CreateMediaAssetCopy` (o nombre equivalente):
  - authorize: `create` MediaAsset + `view`/`update` perímetro del origen
    (**elegido:** `create` + origen en perímetro vía policy `view`).
  - fuerza `company_id = null` (Fase 1b = mismo flujo plataforma).
  - `name`/`alt` = del origen; path nuevo bajo `gallery/{uuid}.ext`.
  - **no** toca el origen.
- **Descartado:** reutilizar `store` genérico con `source_id` opcional (más
  ambigüedad name/alt multi-upload).

### D3 — UX

- Botón/icono Crop en card (junto copiar/editar/borrar), gated `can.create`
  (y origen visible).
- Modal: preview crop + zoom (+ aspect libre por defecto; sin ratios fijos
  obligatorios en 1b).
- Confirmar → POST copies → redirect/reload Index; toast éxito.
- i18n es/en/ca.

### D4 — Authz / tenancy

- Misma política de listado/perímetro que Fase 1.
- Origen fuera de perímetro → 403.
- `company_id` non-null en body → prohibited.

### D5 — Vinculaciones

| V | ¿Sí? | Nota |
|---|---|---|
| V-07/08/21 | Ya cubiertos Fase 1 | sin Functionality nueva |
| resto | No | |

## Risks / Trade-offs

- [CORS/canvas tainted si URL cross-origin] → Mitigation: misma origin
  `Storage::url` public local; tests con Storage::fake.
- [Calidad JPEG] → Mitigation: quality ~0.92 en canvas; documentar.
- [Blob grande] → Mitigation: mismos `max_kilobytes` que upload.

## Migration Plan

- Solo código + npm; sin migración BD.
- Rollback: revert PR / quitar dep.

## Excepciones a standards

_(ninguna nueva; sigue excepción ServerTable del Index)_

## Open Questions

_(ninguna)_
