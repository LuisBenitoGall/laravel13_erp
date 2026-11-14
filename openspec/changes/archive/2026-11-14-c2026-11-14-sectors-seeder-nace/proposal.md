# Proposal

## Why

El `SectorsSeeder` solo tiene 8 sectores genéricos. Hace falta un catálogo
inicial alineado a las secciones NACE Rev. 2 / CNAE (A–S), traducido
ES/EN/CA, sin tocar el CRUD ya entregado.

## What Changes

- Ampliar `database/seeders/SectorsSeeder.php` a las **19 secciones**
  NACE A–S (sin T/U) con slug + name `{es,en,ca}`.
- Upsert por slug (restore soft-deleted; Active).
- Ejecutar el seeder en apply.
- Slugs viejos no NACE: **no borrar** en este change.
- Sin cambios de CRUD/UI/API.

## Capabilities

### New Capabilities

- `sectors-seeder-nace`: datos seed NACE A–S ES/EN/CA vía `SectorsSeeder`.

### Modified Capabilities

- _(ninguna — el contrato CRUD de `admin-sectors-catalog` no cambia)_

## Impact

- Solo `SectorsSeeder` (+ test de seed opcional si aporta).
- BD local/dev tras `db:seed --class=SectorsSeeder`.
