# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. Incluye platform-roles, company-custom-roles, company-role-assignment,
> form-kit y `c2026-10-26-platform-console-mvp` (consola operador perímetro).

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager; CRUD roles de plataforma y custom tenant; asignación User↔rol
local; kit Form MVP; **consola plataforma MVP**: listado tenants del perímetro
(`user_companies`), elegibilidad de módulos solo sobre companies vinculadas, menú
grupo «Plataforma» para Super Admin.

**No entra:** facturación SaaS (Cashier/Stripe/cuotas); ver todas las companies sin
vínculo; invitar miembros no vinculados; engorde Company; Wysiwyg/Dropzone/LangTabs.

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| `Company::forUser` | Scope perímetro `user_companies` (SA inclusive) |
| Module Basic `workspace` | Cáscara tenant |
| Roles Spatie `manager` / `guest` | Plantilla universal + copia local |
| Roles plataforma / custom | Admin CRUD / company CRUD |
| `AssignCompanyRole` | Cualquier rol local; un rol por company |
| Functionality `roles` / `company-roles` | Plataforma / workspace |
| Consola plataforma | Partners Companies Index (CTA) + authorizations scoped |

## Sync manager

Disparadores: autorizar / desautorizar / auto-Basic. **No** al encender/apagar.
AdminOnly nunca entra en el manager. CRUD company-roles no altera permisos del manager
local.

## Vinculaciones — platform-console-mvp

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | No | Sin permisos nuevos |
| V-08 | Sí | Menú grupo Plataforma (labels) |
| V-09 | No | Reutiliza módulo `admin` |
| Resto | No | Billing, stock, etc. fuera |
| E2E | N/A | `e2e.md` §4 |

## Dependencias

Admin → Partners (lectura `Company`). Sin cambio de module-map.
