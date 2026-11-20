# Proposal

## Why

El modal de crop de Galería usa `react-easy-crop` (pan + zoom). Los actores
esperaban redimensionar la rejilla con asas; hoy no hay ayuda ni control de
ratio, y la UX resulta confusa. Hay que aclarar el modelo de uso y ofrecer
ratios 1:1 / 4:3 / 16:9 (+ libre).

## What Changes

- Texto de ayuda i18n en el modal (mover imagen + zoom; zona clara = lo guardado).
- Selector de aspect: **1:1**, **4:3**, **16:9**, **libre**.
- Sin cambiar librería ni el contrato `POST …/copies`.
- Tests de fuente/UI (ratios presentes, ayuda presente); sin E2E browser.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `admin-gallery`: requisitos de ayuda y selectores de ratio en el modal crop.

## Impact

- `resources/js/Pages/Admin/Gallery/CropDialog.tsx` (+ i18n es/en/ca).
- Spec delta `admin-gallery`; ficha Admin si hace falta una línea UX.
- E2E: N/A.
