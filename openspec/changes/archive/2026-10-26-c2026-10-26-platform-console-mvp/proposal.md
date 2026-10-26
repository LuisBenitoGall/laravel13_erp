## Why

Fase 0 pide consola de plataforma (operador SaaS). Ya hay piezas (autorizaciones de
módulo, roles plataforma, listado Partners de companies), pero el Super Admin puede ver
operar companies **sin vínculo** (p. ej. authorizations carga todos los Active; table de
companies sin filtro `user_companies`), en tensión con el kernel. Falta un MVP coherente
de operador (tenants del perímetro + elegibilidad) sin billing. Cierra el shell antes de
Fase 1.

## What Changes

- Experiencia operador Super Admin: menú/agrupación “Plataforma” (tenants + elegibilidad;
  roles plataforma ya existen).
- Listado tenants/companies del **perímetro vinculado** (ServerTable); enlace a
  elegibilidad de esa company.
- Endurecer `company-module-authorizations`: selector y authorize/revoke solo sobre
  companies vinculadas al actor; 403 si no.
- Aplicar el mismo perímetro al listado/table de `Company` cuando el actor es Super Admin
  (y, en defensa, a cualquier listado de companies: solo las de `user_companies`).
- Reutilizar Actions Authorize/Revoke existentes; sin dominio Platform nuevo obligatorio.
- **No** Cashier/Stripe, cuotas, RGPD, “ver todas las companies”.

## Capabilities

### New Capabilities

- `platform-console`: consola operador MVP (listado tenants perímetro + flujo a
  elegibilidad; menú plataforma).

### Modified Capabilities

- `company-module-layers`: UI/operación de elegibilidad acotada al perímetro vinculado.
- `company-tenancy`: reforzar que listados de Company respetan vínculo (incl. Super Admin).

## Impact

- `CompanyModuleAuthorizationController` (+ tests perímetro)
- `CompanyController@table` / Policy o scope de visibilidad
- Menú Admin (MenuBuilder / seed labels)
- Posible página Index “tenants” o reuso Partners Index con CTA a authorizations
- `docs/modules/Admin.md`
- Feature tests; E2E N/A
