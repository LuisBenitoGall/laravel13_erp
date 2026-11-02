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
vínculo; invitar miembros no vinculados; engorde Company; Wysiwyg/Dropzone;
menú/Index/CRUD genérico de Funcionalidades; soft delete de módulos shell
(`admin`/`workspace`).

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
| Menú pilares | Empresas (Mis empresas), Usuarios (Usuarios empresa→Members sesión vía `module:workspace` + `company-users.*`), Configuración SA (Módulos/Roles/Permisos→catálogo Spatie; sin ítem genérico Funcionalidades) |
| Catálogo Permisos | Index ServerTable (name, Functionality vía prefijo, Acciones Delete custom); Create modal (`{slug}.{sufijo}` único; reserved 5+access); canónicos + `*.access` no borrables; Functionality `permissions` + Policy; sin Edit ni asignación Roles/Users |
| Shell topbar | Bloque 1: collapse izq. / acciones der.; stubs popover; perfil+logout |
| Shell topbar polish | Mini-rail md+ (`erp.aside.collapsed`); móvil off-canvas; flyout B1; logos reducidos; notas=Popover |
| Shell breadcrumb | Bloque 2: `PageBreadcrumb` option (Link) + suboption; Index → `dashboard` |
| Shell action bar | Bloque 3: `ViewActionBar` (Volver con `backHref` fijo o origen, CTAs, delete+confirm); ≠ FormActions / Records/ActionBar |
| Listado Modules | Referencia `ServerTable`: name, color, icon (Lucide + `title`), level (i18n), created_at y Acciones (toggle solo `optional` → Edit → Delete confirm en no-shell; sin Delete en shell; sin columna Estado ni búsqueda avanzada). Inactive permitido también en shell vía UpdateModule. Export xlsx/pdf por el mismo `table` |
| Create Module | Alta con LangTabs (label+explanation; principal required), IconPicker, ColorPicker, Switch status default Active; slug solo servidor + `{slug}.access`; redirect Edit. Kit: `Form/LangTabs`, `ColorPicker`, `IconPicker` |
| Edit Module | Tabs Módulo \| Funcionalidades; form kit; slug inmutable/oculto; level readonly i18n; `backHref=/admin/modules`; RecordSummary; ActionBar Nueva funcionalidad + Delete (no shell) |
| Soft delete Module | Index Acciones + Edit ActionBar; Spatie intacto; shell `admin`/`workspace` → 403 |
| Functionalities anidadas | ServerTable (label, name, Acciones toggle→Edit→Delete; sin slug; `exportable=false`); modales Create (name+LangTabs label; slug auto) / Edit (solo label); soft delete sin purge Spatie; `defaultLocale` shared; sin CRUD genérico ni `ModuleForm` |

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

## Vinculaciones — admin-permissions (catálogo Spatie)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Reutiliza Functionality `permissions` + Spatie `permissions.*` (ya sembrable) |
| V-08 | Sí | Ítem Permisos → Index catálogo (deja temporal→Roles) |
| V-09 | No | Módulo `admin` existente |
| R-TEN-05 | N/A | Permission global Spatie, sin `company_id` |
| E2E | N/A | `e2e.md` §4; feature tests del CRUD |

## Dependencias

Admin → Partners (lectura `Company`). Sin cambio de module-map.
