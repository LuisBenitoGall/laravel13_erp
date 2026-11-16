# Proposal

## Why

El icono de notas del topbar es stub y el dashboard no muestra post-its.
Hay que portar las notas internas personales de v1 (modal alta + post-its
en dashboard), sin scope de tenant.

## What Changes

- Modelo `Note` / `notes`: `user_id`, `title`, `note` (sin `company_id`,
  sin `status`).
- Topbar: icono → **modal** formulario (título + nota) → store; badge
  con conteo; tope 50/user.
- Dashboard: post-its (fecha, título, cuerpo, delete); «Ver todas» →
  dashboard.
- Sustituir `NotesStub`; delta `shell-topbar` (notas reales; resto stub
  donde aplique).
- Excepción documentada: modal centrado para notas (no solo popover).

## Capabilities

### New Capabilities

- `user-notes`: Note personal + modal topbar + post-its dashboard.

### Modified Capabilities

- `shell-topbar`: icono notas abre modal real (no empty stub).

## Impact

- Migración/modelo/Policy/Actions; rutas store/destroy.
- Topbar Notes + Dashboard props `notes`.
- Tests ownership, tope 50, sin filtro company.
- Glosario Nota interna / `Note` (≠ DeliveryNote).
