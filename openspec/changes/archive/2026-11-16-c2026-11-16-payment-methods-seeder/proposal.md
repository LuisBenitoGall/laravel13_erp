# Proposal

## Why

El catálogo `PaymentMethod` está vacío tras el CRUD. Hace falta un set
inicial usable (v1 limpio + Compensación/Bizum), traducido ES/EN/CA,
sin tocar el modelo MVP ni el UI.

## What Changes

- `PaymentMethodsSeeder`: 12 métodos (ids 1–12), name `{es,en,ca}`,
  status Active; upsert por id; no borra filas ajenas.
- Set: Contado en efectivo, Cheque/pagaré (fusión), Confirming,
  Domiciliación bancaria, Recibo, Talón, Transferencia (única),
  Tarjeta de crédito, A concretar, Abono de tarjetas en banco,
  Compensación, Bizum.
- Sin `code`/`slug`; sin pivot; sin cambios CRUD UI.
- Test de seed idempotente; nota en Admin.md / spec.

## Capabilities

### New Capabilities

- `payment-methods-seeder`: datos canónicos ids 1–12 vía seeder.

### Modified Capabilities

- `admin-payment-methods-catalog`: catálogo poblable por seeder sin
  ampliar modelo MVP.

## Impact

- `database/seeders/PaymentMethodsSeeder.php` (+ test Feature/Unit).
- Docs Admin.md; ejecución `db:seed --class=PaymentMethodsSeeder`.
