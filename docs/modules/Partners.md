# Módulo Partners — empresas (shell / tenancy / roles)

> Ficha playbook §1. **Como quedó** tras tenancy + `c2026-10-09-company-roles`
> + Index Mis Empresas (`c2026-11-12-mis-empresas-table`) + Create mínimo
> (`c2026-11-12-company-create-form`) + Edit tabs (`c2026-11-12-company-edit-form`).

## Propósito y alcance

**Entra:** `Company` shell; `user_companies`; sesión/modal; CRUD; al crear company:
roles `manager`/`guest`, creador vinculado y con `manager`; UI miembros
(`partners/companies/{id}/members`) para asignar manager|guest; Index **Mis
Empresas** canónico; Create mínimo (name / tradename / nif / status); Edit con
tabs Info + Imágenes.

**No entra:** CustomerProvider, obras, contactos, roles custom; tab Empresas de
ficha User (membership `user_companies` ≠ este Index); **pago por nueva
empresa** (change futuro); tab Miembros en el Edit (change futuro; ruta
Members puede existir sin enlace desde Edit).

## Entidades

| Entidad | Notas |
|---|---|
| `Company` | Sin `BelongsToCompany` (es el tenant). Index Mis Empresas: ServerTable canónico. Create: name/tradename/nif/status; slug auto `UniqueSlug`; NIF required único+formato max 12. **Edit:** `RecordEditLayout` tabs Info/Imágenes; Info = `CompanyProfileReadout` (logo ▭ centrado V+H) + form name/tradename/nif≡Create/status; slug fuera de UI y **regenerado en cada Update** (`whereKeyNot` self); Imágenes = un solo logo POST `partners.companies.logo` ≤512 KB Request / 500 KB UI → `companies.logo`; aside si CurrentCompany; sin link Miembros |

## Roles (como quedó)

- `CreateCompany::handle(array $data, User $creator)` crea roles vía Admin Action,
  adjunta al creador y le asigna `manager`, luego Basic authorize + sync.
- Asignación posterior: Super Admin o `manager` de esa company (`CompanyRoleGate`).

## Seed

Company `ACME` (slug `acme`, NIF `00000000A`) + superadmin como manager + workspace
Basic; backfill de companies existentes en `AdminCatalogSeeder`.
