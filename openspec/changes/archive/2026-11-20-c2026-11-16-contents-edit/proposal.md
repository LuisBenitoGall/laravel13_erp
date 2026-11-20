# Proposal

## Why

Tras Create, el Edit de Contents es un placeholder. Hay que poder
redactar textos multiidioma (y activar el registro) con cabecera de
ficha + historial, sin poder cambiar el tipo (`kind`) elegido en el alta.

## What Changes

- Sustituir Edit placeholder por formulario real según `kind`.
- Cabecera `RecordSummary`: título del registro, badge `code`, campos
  RO de tipo + fechas creación/actualización (historial).
- `kind` y `code` inmutables en Edit (tipo visible, no editable).
- Campos editables: name, status, title/excerpt/(content) Spatie,
  referrer/video como JSON multi-valor (UI lista; encima de
  traducciones); HTML allowlist; invalidar HelpTip cache.
- Badge `code` en cabecera = identificador técnico (HelpTip), no título.
- Sin galería, sin `published_*`, sin CSS/tags/links v1.

## Capabilities

### New Capabilities

- `contents-edit`: Edit real de Content (cabecera + form por kind).

### Modified Capabilities

- `contents-create`: elimina el requirement de Edit placeholder
  (sustituido por `contents-edit`).

## Impact

- Front: `Admin/Contents/Edit.tsx` + form; `RecordSummary`.
- Backend: Update Action/FormRequest; props Edit amplias; cache forget.
- Docs Admin/glossary si hace falta matizar Edit.
- Sin deps npm nuevas.
