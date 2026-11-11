# Proposal

## Why

El tab Emails y teléfonos ya gestiona emails adicionales; la columna de
teléfonos sigue vacía. Hace falta N teléfonos por User (cards + modal
alta/edición) sobre una tabla polimórfica `phones` reutilizable por otros
owners más adelante.

## What Changes

- Schema `phones` polimórfico (`phoneable`) según migration acordada
  (e164, ext, type, label, flags, notes, softDeletes, índices/unique).
- Columna derecha del tab contactos: cards de teléfonos (+ alta, edit,
  delete, tel, copy, WhatsApp, primario); badge verificación solo
  informativo.
- UI/API de este change **solo** para `User`; `country_id` null en users.
- Dominio Admin: modelo `Phone`, Policy, Actions, FormRequests, controller;
  relación morph en User; props Edit + readout.
- Authz igual que emails (self o `company-users.update` + perímetro).
- Glosario: término `Phone` / teléfono.

## Capabilities

### New Capabilities

- `user-phones`: teléfonos del User vía `phones` polimórfica (CRUD
  alta/edición/baja, primario único, UI cards en ficha).

### Modified Capabilities

- `user-edit`: tab contactos — columna teléfonos operable (deja de estar
  vacía).
- `user-emails`: deja de exigir placeholder vacío en la columna derecha
  (emails intactos; layout dos columnas compartido).

## Impact

- Migración `phones`; modelo `Phone`; `User::phones()`.
- Rutas profile + company-users nested phones (store/update/destroy +
  marcar primario).
- Front: panel contacts (reemplazar placeholder teléfonos).
- Tests Feature; `docs/modules/Admin.md`; `glossary.md`.
