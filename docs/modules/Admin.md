# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. Incluye `c2026-10-17-platform-roles` (CRUD roles de plataforma).
> Base previa: tenancy, company-roles, sync manager, auth-onboarding, i18n.

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager con autorizaciones Basic/Optional; **CRUD Super Admin de roles de
plataforma** (universales vs genéricos; matriz de permisos; plantillas no borrables).

**No entra:** roles custom creados por el manager del tenant; asignación User↔rol en UI
de plataforma (salvo onboarding → manager); Platform/pago; engorde Company.

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| Roles Spatie `manager` / `guest` | Plantilla universal (`company_id` NULL) + copia local por company |
| Roles plataforma | `company_id` NULL; `universal` true\|false; `description` |
| `EnsureCompanyDefaultRoles` | Materializa universales al alta de company |
| `SyncManagerPermissionsForCompany` | Resync idempotente (excluye AdminOnly); no techo por plantilla |
| `AssignCompanyRole` | Solo `manager`\|`guest` a users vinculados |
| Functionality `roles` | Menú Admin; CRUD solo Super Admin |

## Sync manager

Disparadores: autorizar / desautorizar / auto-Basic. **No** al encender/apagar.
AdminOnly nunca entra en `role_has_permissions` del manager. Techo tenant = módulo
autorizado+activado (todas las funcionalidades); la matriz de la plantilla universal
`manager` no recorta.

## Vinculaciones (V-01..V-20) — platform-roles

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Permisos `roles.*` |
| V-08 | Sí | Menú Admin → Roles |
| V-09 | No | Reutiliza módulo `admin` existente |
| Resto | No | Stock, ledger, PDF, portal, etc. no aplican |

## Dependencias

Admin → Partners (lectura `Company`). Sin cambio de module-map.
