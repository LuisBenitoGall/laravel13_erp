# Design

## Context

Hoy `Form/GalleryInsertDialog` encapsula fetch paginado, debounce,
AbortController, grid e infinite scroll; solo lo monta `Form/Wysiwyg`
cuando `enableGalleryInsert`. El endpoint `admin.gallery.picker` ya
está endurecido (Fase 2b). Motivación: proposal.md — Why. Alcance A:
extraer, no cablear segundo consumidor.

## Goals / Non-Goals

**Goals:**

- Componente `Form/MediaPicker` exportado y documentado.
- Wysiwyg = único consumidor actual vía MediaPicker.
- Misma UX/contrato de red; sin regresión de inserción.

**Non-Goals:**

- Segunda pantalla; upload empresa; Index paginado; thumbnails;
  Help / URL libre / editar alt.

## Decisions

### D1 — Nombre y ubicación canónicos

- **Decisión:** `Form/MediaPicker` (+ tipo `MediaPickerAsset` o
  reexport del shape `{ id, name, alt, url }`). Entrada en
  `docs/standards/glossary.md` (UI: selector de Galería / MediaPicker)
  y fila en catálogo `frontend.md`. Export en `Form/index.ts`.
- **Por qué:** brief Fase 3; alineado a IconPicker/Dropzone en el kit.
- **Alternativa:** dejar nombre GalleryInsertDialog — no cumple “genérico”.

### D2 — Props del MediaPicker

- **Decisión:** controlado por `open` / `onOpenChange` /
  `onSelect(asset)`. Sin acoplarse a TipTap ni a Content. Título i18n
  genérico (reutilizar claves de galería insertar o clave
  `media_picker_*` si 20 prefiere desacoplar copy del editor — preferir
  reutilizar las existentes si el texto sigue siendo «Elegir imagen…»).
- **Por qué:** mismo patrón Dialog que hoy; consumidores futuros
  solo montan el picker.

### D3 — Migración desde GalleryInsertDialog

- **Decisión:** mover/renombrar la implementación a `MediaPicker.tsx`;
  Wysiwyg importa MediaPicker; borrar `GalleryInsertDialog.tsx` (o
  reexport deprecado una release — preferir **borrar** en el mismo
  change: un solo consumidor).
- **Por qué:** evitar dos APIs paralelas.

### D4 — Backend

- **Decisión:** **cero** cambios en `picker` salvo bug descubierto.
  Contrato vigente se documenta como fuente de datos del MediaPicker.
- **Por qué:** brief «no rediseñar la API».

### D5 — Specs

- **Decisión:** ADDED requirement MediaPicker en `form-kit`; MODIFIED
  Wysiwyg para exigir que el selector sea el MediaPicker (mantener
  scenarios de búsqueda/scroll e inserción).
- **admin-gallery / contents-edit:** sin delta obligatorio (comportamiento
  observable del endpoint y del Article no cambia).

### D6 — Tests / E2E / vinculaciones

- Assert fuente: existe `MediaPicker`, Wysiwyg lo referencia, no queda
  `GalleryInsertDialog` (o no se usa). Feature HTTP picker intactos.
- E2E: N/A. V-01..V-20: todos no.

### D7 — Excepciones a standards

Ninguna.

## Risks / Trade-offs

- [Rename rompe imports futuros] → Mitigación: un solo call site hoy;
  export público estable `MediaPicker`.
- [Copy i18n demasiado “insertar en editor”] → Mitigación: títulos
  genéricos «Elegir imagen de la galería» ya encajan; no inventar flujo
  nuevo.

## Migration Plan

1. Crear `MediaPicker`, cablear Wysiwyg, borrar dialog viejo.
2. Exports + docs (glossary, frontend, ficha Admin Fase 3).
3. Tests fuente + suite picker/Content afectados.
4. Pint / tsc si aplica.

Rollback: revert front; sin BD.

## Open Questions

_(ninguna)_
