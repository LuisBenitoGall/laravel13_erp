# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. Incluye platform-roles, company-custom-roles, company-role-assignment,
> form-kit, platform-console-mvp, shell-navigation y `c2026-10-28-shell-topbar`.

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager; CRUD roles de plataforma y custom tenant; asignación User↔rol
local; kit Form MVP; consola plataforma MVP (tenants perímetro); **shell**: Dashboard
fijo, logos ERP+company, bloque Configuración SA (AdminOnly sin exigir
authorized∩enabled), bypass `EnsureModuleIsActive` solo AdminOnly+SA (perímetro de
datos intacto).

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
| Shell navegación | Dashboard + Configuración SA; seed ACME; post-login → dashboard |
| Menú pilares | Empresas (Mis empresas), Usuarios (Usuarios empresa→Members sesión vía `module:workspace` + `company-users.*`), Configuración SA (Módulos/Roles/Permisos→Roles). CRUD permisos Spatie custom = change futuro |
| Shell topbar | Bloque 1: collapse izq. / acciones der.; stubs popover; perfil+logout |
| Shell topbar polish | Mini-rail md+ (`erp.aside.collapsed`); móvil off-canvas; flyout B1; logos reducidos; notas=Popover |
| Shell breadcrumb | Bloque 2: `PageBreadcrumb` option (Link) + suboption; Index → `dashboard` |
| Shell action bar | Bloque 3: `ViewActionBar` (Volver si origen, CTAs, delete+confirm); ≠ FormActions / Records/ActionBar |

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
