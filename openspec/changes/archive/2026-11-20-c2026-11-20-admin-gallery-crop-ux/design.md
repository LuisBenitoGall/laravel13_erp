# Design

## Context

Brief `docs/prompts/c2026-11-20-admin-gallery-crop-ux.md`. Fase 1b crop→copia
ya en producción local; lib `react-easy-crop` (modelo pan+zoom, aspect default
4/3). Luis eligió A + ayuda/ratios (no cambiar lib).

E2E browser: **no aplica**.

## Goals / Non-Goals

**Goals:** ayuda visible + ratios 1:1 / 4:3 / 16:9 / libre en `CropDialog`.

**Non-Goals:** otra lib; resize de caja; cambio API copies; Wysiwyg/picker;
inputs ancho×alto.

## Decisions

### D1 — Ratios y `aspect`

| Clave UI | `aspect` prop |
|---|---|
| 1:1 | `1` |
| 4:3 | `4/3` |
| 16:9 | `16/9` |
| libre | ratio natural de la imagen (`width/height` de `asset` si hay dims; si no, `4/3` fallback) |

- Default al abrir modal: **4:3** (alineado al default histórico de la lib).
- Al cambiar ratio: reset razonable de `crop` a `{x:0,y:0}` y opcionalmente
  `zoom` a `1` para evitar encuadre raro.

### D2 — Ayuda

- Párrafo corto bajo el cropper / junto al zoom (i18n
  `galeria_recortar_ayuda`): pan + zoom; zona clara = área guardada; no se
  estira la rejilla con asas.
- Labels ratios: `galeria_ratio_1_1`, `_4_3`, `_16_9`, `_libre`.

### D3 — UI controles

- Grupo de botones toggle (o segmented) encima o debajo del slider zoom;
  gated solo UI (sin permiso nuevo).
- Sin dependencias npm nuevas.

### D4 — Backend

- **Sin cambios** de Action/ruta/policy.

## Risks / Trade-offs

- [“Libre” malentendido como resize de caja] → Mitigation: ayuda explícita.
- [Dims null en asset viejo] → Mitigation: fallback 4/3.

## Migration Plan

- Solo front + i18n. Rollback = revert.

## Open Questions

_(ninguna)_
