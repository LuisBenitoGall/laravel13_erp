# Proposal

## Why

El Edit de roles de plataforma no sigue el contrato de v1: layout estrecho y
matriz por submit único. Hay que alinear layout, toggles ágiles, Guardar =
solo descripción, y CTA copiar a companies del perímetro.

Fuente canónica: `docs/prompts/c2026-11-05-platform-roles-edit.md`.

## What Changes

- Edit a ancho completo; organización de permisos como v1 (anclas Module,
  bloques/cards Functionality).
- Persistencia por checkbox (acción o Functionality completa) sin recarga;
  fallo → revert + toast.
- ≥1 permiso de Functionality bajo un Module ⇒ `{module}.access` en el rol;
  un permiso no implica otras acciones ni otras Functionalities.
- Guardar solo descripción (no matriz).
- CTA Copiar (no Super Admin, no universal) → rol(es) local(es) en companies
  del perímetro; colisión de `name` → versionar.
- Delta `platform-roles` (ADDED + MODIFIED matriz).

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `platform-roles`: Edit v1-like; toggles; auto access; Guardar=descripción;
  copy.

## Impact

- Pages `Admin/Roles/Edit` (+ componentes); endpoints toggle/copy; Actions;
  tests Feature; ficha `docs/modules/Admin.md`.
- Company-roles Edit: fuera (reuso futuro).
- E2E: no aplica. Ficha módulo nueva: no aplica.
