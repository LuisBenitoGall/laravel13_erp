## Context

Brief: `docs/prompts/c2026-10-26-platform-console-mvp.md`. Kernel + `company-tenancy`:
Super Admin no bypassa perímetro de datos. Hoy
`CompanyModuleAuthorizationController@index` lista todas las Active;
`authorize`/`revoke` no comprueban vínculo; `CompanyController@table` usa
`Company::query()` sin filtro (SA via Gate::before ve todo).

## Goals / Non-Goals

**Goals:** menú operador; tenants perímetro; elegibilidad acotada; tests perímetro.

**Non-Goals:** Stripe/Cashier; ver-all; engorde Company; nuevo bounded context Platform
completo; Form kit / roles CRUD.

## Decisions

1. **Sin dominio `Platform` nuevo**  
   Rutas bajo Admin (`/admin/...`) + menú grupo i18n «Plataforma» visible a Super Admin
   (módulo `admin` AdminOnly). Alternativa descartada: paquete/dominio Platform vacío.

2. **Scope de visibilidad de Company**  
   Helper/scope reutilizable, p. ej. `Company::query()->forUser(User $user)` =
   `whereHas('users', id = $user->id)`. Usar en:
   - `CompanyController@table` (y cualquier index de companies)
   - `CompanyModuleAuthorizationController@index` (selector)
   - Policy `view`/`update`/`delete`: además del permiso, exigir vínculo (SA incluido;
     Gate::before bypasa can() pero **no** debe saltarse un check explícito en
     controller/scope — el scope en query es la línea principal; en authorize/revoke
     `abort_unless($user->companies()->where(...)->exists())`).  
   Alternativa descartada: solo documentar sin código.

3. **Listado tenants operador**  
   Reusar `Partners/Companies/Index` + table ya scoped **o** ruta Admin
   `platform/tenants` que renderiza el mismo Index con prop `operator: true` y columna/CTA
   «Autorizaciones» → `/admin/company-module-authorizations?company_id=`. Preferencia:
   **reusar Partners Index** + CTA si SA, para no duplicar ServerTable. 20 elige la
   variante mínima que cumpla el scenario.

4. **Elegibilidad**  
   Mantener Actions `AuthorizeModuleForCompany` / `RevokeModuleAuthorizationForCompany`.
   Solo filtrar companies del selector + gate de vínculo en POST. UI puede ganar link
   “volver a tenants” (i18n).

5. **Menú**  
   Entradas existentes (roles, authorizations, companies) agrupadas bajo label
   Plataforma / Administración para SA; no inventar Functionality nueva si ya hay
   `companies`, `company-module-authorizations`, `roles`. Ajuste MenuBuilder/labels.

6. **E2E**  
   N/A (`e2e.md` §4). Feature: SA ve solo vinculadas; 403 authorize company ajena;
   authorize OK en vinculada.

7. **Ficha Admin**  
   Quitar “Platform” del No entra en la medida del MVP (sin pago).

## Risks / Trade-offs

- **[Riesgo]** Managers con `companies.viewAny` si algún día AdminOnly se relaja —
  el scope `forUser` les protege. **Mitigación**: scope universal en table.
- **[Trade-off]** Reusar Partners vs página Admin dedicada — menos código vs separación
  visual; CTA + menú bastan para “separación conceptual”.
- **[Riesgo]** Seeds: SA demo debe seguir vinculado a demo company o el listado queda
  vacío. **Mitigación**: verificar seeder (ya attach).

## Migration Plan

1. Scope/helper + tests unitarios ligeros.  
2. Wire controllers + policy.  
3. Menú + CTA UI.  
4. Feature tests perímetro.  
5. Docs ficha Admin.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Ninguna (alinea código al kernel ya escrito).

## E2E

No aplica.
