# Proposal

## Why

Tras el CRUD de Delegaciones, falta el **puente operativo** entre sedes
(Workplace de la matriz) y sociedades hijas (Delegaciones). Sin ese mapeo N:M
no hay default de sociedad por sede ni base limpia para el dropdown de
documentos en oleadas posteriores.

## What Changes

- Tabla/pivote N:M `workplace` ↔ Company delegación (`parent_id` = matriz),
  con flag de **preferida** (≤1 por workplace, opcional).
- Gestión del mapeo en la ficha Edit de Workplace (matriz en sesión); selector
  solo entre hijas de la matriz.
- Tests de perímetro, N:M y preferida. Sin cambios en pedidos ni switcher.

## Capabilities

### New Capabilities

- `workplace-delegation-map`: vínculo N:M sede–delegación + preferida.

### Modified Capabilities

- `workplaces`: Edit gestiona vínculos a delegaciones.
- `company-delegations`: las hijas son el catálogo elegible del mapeo
  (sin cambiar el CRUD de Delegaciones en sí, solo contrato de uso).

## Impact

- Migration pivote, modelos/relations, FormRequest/Action sync vínculos.
- UI Edit Workplace (multi-select + preferida).
- i18n. E2E §4: N/A.
- No toca `company_accounts`, switcher ni documentos comerciales.
