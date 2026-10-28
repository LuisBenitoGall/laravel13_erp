# Módulo Partners — empresas (shell / tenancy / roles)

> Ficha playbook §1. **Como quedó** tras tenancy + `c2026-10-09-company-roles`.

## Propósito y alcance

**Entra:** `Company` shell; `user_companies`; sesión/modal; CRUD; al crear company:
roles `manager`/`guest`, creador vinculado y con `manager`; UI miembros
(`partners/companies/{id}/members`) para asignar manager|guest.

**No entra:** CustomerProvider, obras, contactos, roles custom.

## Entidades

| Entidad | Notas |
|---|---|
| `Company` | Sin `BelongsToCompany` (es el tenant) |

## Roles (como quedó)

- `CreateCompany::handle(array $data, User $creator)` crea roles vía Admin Action,
  adjunta al creador y le asigna `manager`, luego Basic authorize + sync.
- Asignación posterior: Super Admin o `manager` de esa company (`CompanyRoleGate`).

## Seed

Company `ACME` (slug `acme`, NIF `00000000A`) + superadmin como manager + workspace
Basic; backfill de companies existentes en `AdminCatalogSeeder`.
