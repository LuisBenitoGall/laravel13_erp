# Proposal

## Why

El tab Emails y teléfonos de la ficha de usuario está vacío. Hace falta gestionar
emails adicionales (N por user) con UI de cards, mientras teléfonos queda para
después. El schema actual impide N filas (`unique` sobre `user_id`).

## What Changes

- Columna izquierda del tab: emails adicionales (alta modal, cards, mailto,
  copiar, eliminar; sin edit).
- Columna derecha: placeholder teléfonos.
- Schema: quitar `unique(user_id)`; mantener `email` unique en `user_emails`;
  validar distinto de `users.email` del mismo user.
- Dominio Admin: modelo/relación, Policy, Actions, FormRequests, controller en
  `Domains/Admin` (retirar stub vacío de `app/Http`).
- Props Edit + `UserProfileReadout` reflejan la lista real.

## Capabilities

### New Capabilities

- `user-emails`: emails adicionales del User (CRUD parcial alta/baja, UI cards
  en ficha).

### Modified Capabilities

- `user-edit`: el tab contactos deja de ser solo “próximamente” en emails.

## Impact

- Migración `user_emails`; `User` / `UserEmail`.
- Rutas anidadas bajo user (profile y/o company-users).
- Front: panel tab contacts, modal, cards.
- Tests Feature; docs Admin / frontend si aplica.
