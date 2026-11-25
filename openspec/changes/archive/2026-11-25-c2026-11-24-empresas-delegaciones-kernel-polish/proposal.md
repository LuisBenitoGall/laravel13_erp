# Proposal

## Why

El modelo matriz / Delegación / Workplace ya está implementado (CRUD, switcher,
mapeo N:M), pero el **kernel** aún no lo constituye de forma explícita ni deja
citables las reglas de sociedad en documentos comerciales (aplazadas a
presupuestos/pedidos). La ficha Edit de Delegación tampoco muestra, en solo
lectura, qué sedes de la matriz la tienen vinculada.

## What Changes

- Enmienda normativa: `kernel.md`, `architecture.md` (R-TEN-*), `glossary.md`,
  adaptadores always-on (`01-kernel.mdc`, mención mínima `CLAUDE.md` si hace
  falta) con el modelo de grupo y la sección de invariantes **aplazadas** de
  documentos (dropdown sociedad, bloqueo post-factura, listados de grupo).
- UI: panel RO en Edit Delegación — workplaces de la matriz vinculados vía
  mapeo + indicador preferida; copy que distingue sedes de la hija vs sedes de
  la matriz.
- Tests Pest del panel RO. Sin implementar pedidos ni listados de docs.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `company-delegations`: Edit con panel RO de sedes de matriz vinculadas + copy.
- `workplace-delegation-map`: el mapeo N:M es fuente de datos del panel RO
  (sin cambiar escritura en Edit Workplace).

## Impact

- Docs standards + always-on Cursor.
- `Delegations/Edit` + prop Inertia (lista vínculos desde pivote).
- E2E §4: N/A.
