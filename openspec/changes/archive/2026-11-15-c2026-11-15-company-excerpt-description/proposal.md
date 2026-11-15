# Proposal

## Why

Hace falta texto breve y largo multi-idioma en Company (Edit → Información)
para alimentar más adelante el Directorio. El kit Form aún no tiene Wysiwyg;
este change lo introduce (tiptap) como primer consumidor en `description`.

## What Changes

- Columnas `excerpt` y `description` nullables en `companies` tras
  `parent_id`, traducibles (Spatie).
- Edit tab Información: excerpt (`Textarea` ≤1000) + description
  (`Form/Wysiwyg`); Create sin esos inputs.
- Nuevo `Form/Wysiwyg` + instalar `@tiptap/react` (canon libraries).
- Actualizar `frontend.md` / `libraries.md` / glosario / ficha Admin.
- **No** pintar textos en el Directorio (change siguiente).

## Capabilities

### New Capabilities

_(ninguna — el comportamiento vive en capabilities existentes)_

### Modified Capabilities

- `form-kit`: añadir `Wysiwyg` (tiptap); retirar la exclusión vigente.
- `company-edit`: tab Información con excerpt/description traducibles.

## Impact

- Migración `companies`; modelo `Company` (`HasTranslations`).
- FormRequest/Update + `CompanyForm` Edit; i18n.
- npm `@tiptap/react` (+ extensiones mínimas del starter).
- Tests Feature Edit/Create; docs Admin + standards de kit/libs.
- Sin cambio de menú, Policy nueva ni Directorio UI.
