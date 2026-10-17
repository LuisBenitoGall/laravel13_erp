# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. **Como quedó** tras tenancy (`c2026-10-09-company-tenancy-modules`)
> y roles por empresa (`c2026-10-09-company-roles`).

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager con autorizaciones Basic/Optional.

**No entra:** roles custom creados por el manager; Platform/pago; engorde Company.

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| Roles Spatie `manager` / `guest` | Mismo `name`, distintos `company_id` (Teams) |
| `EnsureCompanyDefaultRoles` | Alta roles por company |
| `SyncManagerPermissionsForCompany` | Resync idempotente (excluye AdminOnly) |
| `AssignCompanyRole` | Solo `manager`\|`guest` a users vinculados |

## Sync manager

Disparadores: autorizar / desautorizar / auto-Basic. **No** al encender/apagar.
AdminOnly nunca entra en `role_has_permissions` del manager.

## Dependencias

Admin → Partners (lectura `Company`).
