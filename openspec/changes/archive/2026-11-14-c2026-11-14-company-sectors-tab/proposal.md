# Proposal

## Why

La atribución Company↔Sector ya existe en el tab Info y se autoriza con
`companies.update`. Hace falta un tab «Sectores» propio y permisos
`company-sectors.view` / `.update` bajo Module `empresas`, de cara al
directorio futuro por sector.

## What Changes

- Mover `CompanySectorsPanel` del tab Info a tab `sectors`.
- Functionality `company-sectors` (Module `empresas`): canónicos;
  tab con `.view`; sync PUT con `.update` + vínculo company.
- Reutilizar pivot `company_sector`, Action y ruta; sin modelo
  `CompanySector`.
- Create intacto.
- Specs/docs/tests de authz y UI tab.

## Capabilities

### New Capabilities

- `company-sectors-tab`: tab Sectores en Edit Company + Functionality
  `company-sectors` (view/update).

### Modified Capabilities

- `company-edit`: tabs incluyen Sectores; panel fuera de Info.
- `admin-sectors-catalog`: authz de atribución pasa a
  `company-sectors.*` (no solo `companies.update`).

## Impact

- Seed/Realign: Functionality bajo `empresas` (sin ítem de menú propio).
- `SyncCompanySectorsRequest` + props `can` en Edit.
- Front: `Edit.tsx` tabs; panel read-only sin `.update`.
- Tests Feature: view/update/403; Create sin panel.
