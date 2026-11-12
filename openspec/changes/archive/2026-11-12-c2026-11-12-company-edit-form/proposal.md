# Proposal

## Why

Tras el Create mínimo, el Edit de Company sigue siendo un formulario plano
con slug editable, logo como URL de texto y un link a Miembros. Hay que
alinearlo al patrón de ficha User (tabs + readout) y cerrar logo/slug/NIF
según lo acordado en explore.

## What Changes

- Edit Company pasa a `RecordEditLayout` con tabs **Info** e **Imágenes**.
- Tab Info: readout izquierdo (logo rectangular centrado V+H + info) +
  form name / tradename / nif / status; sin slug ni logo en UI.
- NIF en Update alineado a Create (required, formato, max 12, único).
- Slug: fuera de UI; regenerado automáticamente en Update con unicidad
  (`UniqueSlug`).
- Tab Imágenes: un solo logo vía file input (sustituye; max 500 KB); si la
  company es la de sesión, el aside refleja `tenant.logo_url`.
- Se quita el link “Gestionar miembros” del Edit (tab Members = futuro).
- Onboarding de la primera empresa no se toca.

## Capabilities

### New Capabilities

- `company-edit`: ficha Edit de Company (tabs Info/Imágenes, form Info,
  upload de logo único, sin Miembros en esta UI).

### Modified Capabilities

- `slug-generation`: regeneración automática de slug también en Update de
  Company (única; fuera de UI).

## Impact

- Front: `Partners/Companies/Edit.tsx`, `CompanyForm` (modo edit), readout
  de empresa (nuevo o local), tab logo.
- Back: `UpdateCompanyRequest`, `UpdateCompany` (+ UniqueSlug), Action/ruta
  upload logo; storage `public`; `HandleInertiaRequests` ya lee
  `companies.logo`.
- Docs: `docs/modules/Partners.md`.
- Tests Feature: update Info/NIF/slug, upload logo, aside solo si sesión.
- Sin migración de esquema salvo que el design lo justifique (columna
  `logo` ya existe).
