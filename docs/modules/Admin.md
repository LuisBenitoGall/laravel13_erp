# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. Incluye platform-roles, company-custom-roles y
> `c2026-10-26-company-role-assignment` (asignación User↔rol local). Base: tenancy,
> sync manager, auth-onboarding, i18n.

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager con autorizaciones Basic/Optional; **CRUD Super Admin de roles de
plataforma** (universales vs genéricos); **CRUD manager de roles custom de la company**
(matriz filtrada por módulos autorizados ∩ activos ∩ ¬AdminOnly; Module Basic
`workspace` + Functionality `company-roles`); **asignación UI de cualquier rol local**
(manager|guest|custom) a usuarios ya vinculados (`AssignCompanyRole` + Members).

**No entra:** invitar / alta de miembros no vinculados; asignación de roles de
plataforma; Form kit; Platform/pago; engorde Company; edición de permisos del manager
local por el tenant.

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| Module Basic `workspace` | Cáscara tenant; menú «Espacio de trabajo» |
| Roles Spatie `manager` / `guest` | Plantilla universal (`company_id` NULL) + copia local por company |
| Roles plataforma | `company_id` NULL; `universal` true\|false; `description` |
| Roles custom (empresa) | `company_id` = company; `universal = false`; CRUD manager |
| `EnsureCompanyDefaultRoles` | Materializa universales al alta de company |
| `SyncManagerPermissionsForCompany` | Resync idempotente (excluye AdminOnly); no techo por plantilla |
| `AssignCompanyRole` | Cualquier rol local a users vinculados; un rol por company |
| Functionality `roles` | Menú Admin; CRUD solo Super Admin |
| Functionality `company-roles` | Menú workspace; CRUD manager (+ SA) |

## Sync manager

Disparadores: autorizar / desautorizar / auto-Basic. **No** al encender/apagar.
AdminOnly nunca entra en `role_has_permissions` del manager. Techo tenant = módulo
autorizado+activado (todas las funcionalidades); la matriz de la plantilla universal
`manager` no recorta. El CRUD company-roles **no** altera permisos del rol local
`manager`.

## Vinculaciones — company-role-assignment

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | No | Sin permisos nuevos; reutiliza `CompanyRoleGate` |
| V-08 | No | Misma pantalla Members |
| V-09 | No | Sin módulo nuevo |
| V-21 | No | Sin rutas de módulo activable nuevas |
| Resto | No | Stock, ledger, PDF, portal, etc. no aplican |
| E2E | N/A | `e2e.md` §4 |

## Dependencias

Admin → Partners (lectura `Company`). Sin cambio de module-map.
