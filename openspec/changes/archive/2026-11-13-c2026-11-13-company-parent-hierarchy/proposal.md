# Proposal

## Why

Las empresas reales forman pirámides (matriz → filiales → delegaciones).
Hoy `Company` es plana: no se puede representar padre/hijos. Hace falta
`parent_id` y un tab en Edit para gestionarlo, sin tocar el Create.

## What Changes

- Migración: `companies.parent_id` nullable FK → `companies.id`.
- Modelo: relación padre/hijos; validación anti-ciclo.
- Authz: solo companies del vínculo del usuario; Policy/Request.
- Edit: tab exclusivo de jerarquía (padre + hijos). Create sin cambios.
- Specs/docs: company-edit + glosario/ficha Admin si aplica.
- **BREAKING** de modelo de datos solo aditivo (columna nueva); Create UI
  intacta.

## Capabilities

### New Capabilities

- `company-hierarchy`: árbol Company vía `parent_id` (0..1 padre, N
  niveles), tab Edit, perímetro user_companies, anti-ciclo.

### Modified Capabilities

- `company-edit`: tabs Info / Imágenes / jerarquía.

## Impact

- BD: migración `parent_id`.
- Admin: Company model/Policy/Actions/Requests/Controller; Edit React.
- Tests Feature: árbol válido, ciclos, perímetro, Create sin UI jerarquía.
- Docs: `docs/modules/Admin.md` (Company).
