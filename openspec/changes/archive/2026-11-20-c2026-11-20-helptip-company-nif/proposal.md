# Proposal

## Why

Ya existe `<HelpTip>` y el mapa `help_tips`, pero no está integrado junto a
los campos como en v1. Hay que poder anclar un Content Help (`code`) al
lado de cualquier control de formulario; el primer caso es el NIF de
empresa (`company-nif`). Además hace falta un camino reproducible para
promover esos Help fijos a otros entornos.

## What Changes

- `Field` del form-kit: prop opcional `helpCode` (≠ hint textual `help`);
  icono info a la derecha del control → HelpTip.
- Company Create/Edit: NIF con `helpCode="company-nif"`.
- Catálogo/export de Help por `code`: fixture JSON + seeder upsert +
  comando artisan de export desde BD (promoción cross-env).
- Reutilizar HelpContentMap / shared `help_tips` (sin rediseño).

## Capabilities

### New Capabilities

- `helptip-fields`: ancla HelpTip en campos + primer consumidor NIF +
  promoción Help por code.

### Modified Capabilities

- `form-kit`: Field acepta `helpCode` junto al control.

## Impact

- Front: `Components/Form/Field.tsx`, `HelpTip` estilo, `CompanyForm`.
- Backend: seeder/fixture/comando export Help; docs Admin/frontend.
- Sin deps npm nuevas.
