# Módulo Partners — empresas (shell / tenancy)

> Ficha playbook §1. **Como quedó** tras `c2026-10-09-company-tenancy-modules`.

## Propósito y alcance

**Entra:** `Company` shell (name, slug, tradename, nif, status, logo); soft delete;
pertenencia `user_companies` (sin soft delete); sesión/modal post-login; CRUD bajo
`partners/companies` con `module:admin`.

**No entra:** `CustomerProvider`, obras, contactos, NIF histórico, engorde fiscal.

## Entidades

| Entidad | Dominio | Notas |
|---|---|---|
| `Company` | `App\Domains\Partners` | **Sin** `BelongsToCompany` (es el tenant); arch ignoring |

## Sesión

- 0 empresas activas → denegar ERP.
- 1 → `current_company_id` automático.
- N → página bloqueante `companies/select`.
- Switcher valida pertenencia; Super Admin **no** bypasa pertenencia.

## Dependencias

- Admin lee Company (module-map D⁵). CreateCompany llama Action Admin
  `AuthorizeBasicModulesForCompany`.
- Seed: company `demo` + vínculo superadmin + authz/enable Module `admin`.
