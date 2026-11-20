# Proposal

## Why

Cada nuevo HelpTip exige tocar React (`helpCode`). Con la convención
`{helpScope}.{fieldKey}` basta crear/activar el Content Información
para que el icono aparezca en Fields ya identificados, sin PR de front.

## What Changes

- Contexto de formulario `helpScope` + resolución automática del code
  en `Field` (override con `helpCode` explícito).
- Icono solo si existe Help Active con el code derivado.
- Migrar `company-nif` → `companies.nif` (fixture/seeder/BD;
  alias temporal opcional).
- Documentar convención en frontend/glosario; alinear CompanyForm.

## Capabilities

### New Capabilities

_(ninguna — amplía helptip-fields / form-kit)_

### Modified Capabilities

- `helptip-fields`: auto-derive + migración NIF a `companies.nif`.
- `form-kit`: Field + helpScope del formulario.

## Impact

- Front: `FormHelpScope` (o equivalente), `Field` resolve code,
  `CompanyForm`.
- Datos: fixture/seeder `companies.nif`; rename Content en BD.
- Docs: frontend.md + glosario.
