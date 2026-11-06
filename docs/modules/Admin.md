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
(`configuracion` (+ Basics `dashboard`/`cuenta`/`usuarios`/`empresas`)).

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| `Company::forUser` | Scope perímetro `user_companies` (SA inclusive) |
| Modules Basic `dashboard`/`cuenta`/`usuarios`/`empresas` | Menú PDF mínimo |
| Roles Spatie `manager` / `guest` | Plantilla universal + copia local |
| Roles plataforma / custom | Admin CRUD / company CRUD |
| Index roles plataforma | ServerTable (name, description, universal, Acciones Edit + Delete confirm si `can_delete`); Super Admin fuera; CTA Crear → `/admin/roles/create`; Create = solo metadatos (name, description, universal; `permissions` prohibited); Edit ancho completo estilo v1 (anclas Module + cards Functionality; plegar/desplegar todos; `ScrollToTop`); matriz = canónicos + custom (`{functionality}.*`, sin `*.access`); permisos por toggle async (`patchJson`) + auto `{module}.access`; Guardar = solo descripción; CTA Copiar (≥2 companies Active, no universales; destinos perímetro sin sesión; permisos = intersección origen ∩ catálogo tenant destino; versionado nombre); plantillas `manager`/`guest` no borrables pero matriz editable; materialización locales siempre `universal=false`; manager local sigue por sync |
| `AssignCompanyRole` | Cualquier rol local; un rol por company |
| Functionality `roles` / `company-roles` | Plataforma / Module `usuarios` |
| Create roles empresa | Metadatos name+description (`RoleMetadataFields` sin universal); sin matriz; authz `company-roles.create`; POST → `company_id` sesión + `universal=false` → redirect Edit; CTA Index → `/company/roles/create` |
| Edit roles empresa | Espejo Edit plataforma: layout anclas/cards; name readonly; Guardar = solo descripción; matriz = catálogo autorizados∩activos∩¬AdminOnly (canónicos+custom); toggles async `patchJson` + `{module}.access`; `manager` local `permissions_readonly`; Index con `company-roles.view` (sin exigir manager; delete sí); sin copy tenant; `can_edit=!universal`; universales bloqueados en edit/toggle; Actions refuerzan perímetro CurrentCompany |
| Consola plataforma | Partners Companies Index (CTA) + authorizations scoped |
| Shell navegación | Dashboard + Configuración SA; seed ACME; post-login → dashboard |
| Menú pilares | Empresas (Mis empresas), Usuarios (Usuarios empresa→`/admin/company-users` ServerTable `user_companies`×sesión; Todos los usuarios→`/admin/all-users` directorio `is_public=true` + máscara notificación; `company-users.*` / `all-users.view` (Index; `.viewAny` = detalle); Members legacy sigue para asignación de rol), Configuración SA (Módulos/Roles/Permisos/Países/Monedas/Bancos/Unidades/Tipos de IVA/Grupos contables; sin ítem genérico Funcionalidades). Bancos: dominio `Banks`, ficha `docs/modules/Banks.md` |
| Index Usuarios empresa | ServerTable name/surname/email; toggle `users.status`; eliminar = detach `user_companies` (no borra User); CTA Crear → `/admin/company-users/create`; fila Edit → Edit stub |
| Create Usuarios empresa | Alta/vínculo `CreateOrLinkCompanyUser`; email required; `is_admin=true`; `is_public` (default true); password solo si «enviar credenciales»; rol = universales ∪ locales (Super Admin solo si actor SA); mails = máscara flash; POST → Edit stub; authz `company-users.create` |
| Edit Usuarios empresa (stub) | Cabecera name/email; sin formulario completo; authz `company-users.viewAny` + perímetro |
| Index Todos los usuarios | ServerTable global filtrado `is_public=true`; sola acción máscara notificación; sin edit/toggle/delete |
| Unidades (Unit) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive; Index ServerTable (name, symbol; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create/Edit; symbol max 4; name unique; Functionality `units` + `units.*`; Configuración→Unidades; `UnitsSeeder` datos v1 (IDs/status preservados; sin translations); Index no importa. V-07/V-08 sí; E2E N/A |
| Tipos de IVA (VatType) | Catálogo global por Country (sin `company_id`); FK `country_id` required (selector todos los países); rate decimal(5,2); RE nullable; soft delete; Index plano con columna País+filtro, name, rate, RE; Acciones toggle→Edit→Delete; modales; Active-first+name; Functionality `vat-types` + `vat-types.*`; `VatTypesSeeder` → España id=66 (tras GeographySeeder); Index no importa. V-07/V-08 sí; E2E N/A |
| Grupos contables (AccountingAccountType) | Catálogo jerárquico PGC (sin `company_id`/status); autoreference/code/name/mode; soft delete; Index ServerTable (code, name, level); Create modal (raíz); Edit solo name (code inmutable); Delete bloqueado si hijos; Show árbol desde BD; Functionality `accounting-account-types`; `AccountingAccountTypesSeeder` + `lang/*/fiscal.php`; V-07/V-08 sí; E2E N/A |
| Geografía (Países) | Catálogo global Country→Province→Town (sin `company_id`); soft delete; status active/inactive; Index drill v1+ServerTable; Functionality `countries`; cascada JSON; seeder datos v1 con IDs preservados; `flag` nullable; Indexes sin columna Estado (toggle en Acciones); drill Map/MapPin ≠ Pencil |
| Monedas (Currency) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive (enum `CurrencyStatus`); Index ServerTable (name, code, number, symbol; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create (name, code, number, symbol, status) / Edit (solo name+symbol; `code`/`number` inmutables); symbol max 8; Functionality `currencies` + `currencies.*`; Configuración→Monedas (SA); `CurrenciesSeeder` datos v1 con IDs preservados (EUR=2, USD=5, GBP=67, CHF=95; Active solo EUR/USD/GBP/CHF); Index no importa (sin efecto lateral en GET). Vinculaciones: V-07 sí, V-08 sí, V-09 N/A; E2E N/A |
| Catálogo Permisos | Index ServerTable (name, Functionality vía prefijo, Acciones Delete custom); Create modal (`{slug}.{sufijo}` único; reserved 5+access); canónicos + `*.access` no borrables; Functionality `permissions` + Policy; sin Edit ni asignación Roles/Users |
| Shell topbar | Bloque 1: collapse izq. / acciones der.; stubs popover; perfil+logout |
| Shell topbar polish | Mini-rail md+ (`erp.aside.collapsed`); móvil off-canvas; flyout B1; logos reducidos; notas=Popover |
| Shell breadcrumb | Bloque 2: `PageBreadcrumb` option (Link) + suboption; Index → `dashboard` |
| Shell action bar | Bloque 3: `ViewActionBar` (Volver con `backHref` fijo o origen, CTAs, delete+confirm); ≠ FormActions / Records/ActionBar |
| Listado Modules | Referencia `ServerTable`: name, color, icon (Lucide + `title`), level (i18n), created_at y Acciones (toggle solo `optional` → Edit → Delete confirm en no-shell; sin Delete en shell; sin columna Estado ni búsqueda avanzada). Inactive permitido también en shell vía UpdateModule. Export xlsx/pdf por el mismo `table` |
| Create Module | Alta con LangTabs (label+explanation; principal required), IconPicker, ColorPicker, Switch status default Active; slug solo servidor + `{slug}.access`; redirect Edit. Kit: `Form/LangTabs`, `ColorPicker`, `IconPicker` |
| Edit Module | Tabs Módulo \| Funcionalidades; form kit; slug inmutable/oculto; level readonly i18n; `backHref=/admin/modules`; RecordSummary; ActionBar Nueva funcionalidad + Delete (no shell) |
| Soft delete Module | Index Acciones + Edit ActionBar; Spatie intacto; shell `configuracion` (+ Basics `dashboard`/`cuenta`/`usuarios`/`empresas`) → 403 |
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
| V-09 | No | Reutiliza módulo `configuracion` |
| Resto | No | Billing, stock, etc. fuera |
| E2E | N/A | `e2e.md` §4 |

## Vinculaciones — admin-permissions (catálogo Spatie)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Reutiliza Functionality `permissions` + Spatie `permissions.*` (ya sembrable) |
| V-08 | Sí | Ítem Permisos → Index catálogo (deja temporal→Roles) |
| V-09 | No | módulo `configuracion` existente |
| R-TEN-05 | N/A | Permission global Spatie, sin `company_id` |
| E2E | N/A | `e2e.md` §4; feature tests del CRUD |

## Vinculaciones — admin-platform-roles-index

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | No | Reutiliza Functionality `roles` / `roles.*` |
| V-08 | No | Menú Roles ya apunta a Index |
| V-09 | No | módulo `configuracion` |
| R-TEN-05 | N/A | Roles plataforma `company_id` NULL |
| E2E | N/A | `e2e.md` §4; feature Index |

## Vinculaciones — admin-countries (geografía)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality `countries` + Spatie `countries.*` (cubre Province/Town) |
| V-08 | Sí | Configuración → Países |
| V-09 | No | Reutiliza módulo `configuracion` |
| R-TEN-05 | N/A | Catálogo global; test cross-company “mismo dato” |
| E2E | N/A | `e2e.md` §4; feature CRUD + cascada |

## Dependencias

Admin → Partners (lectura `Company`). Sin cambio de module-map.


## Menú PDF (backlog)

Mapa MENU_ERP = referencia de trabajo ordenado (1 change ≥ Module; ≥1 por Functionality al abrirla). No sembrar Optional vacíos del PDF en este change.

## Pendiente: reformular company-module-authorizations

Explorado 2026-11-05 (depuración menú). Por ahora se deja la Functionality/ruta
bajo Configuración; **habrá que reformular** la UX/contrato.

Regla ya acordada (Luis), pendiente de change:

- En la UI de elegibilidad (`/admin/company-module-authorizations`) **no** deben
  aparecer Modules `Basic`: no se autorizan ni se revocan; están siempre a
  disposición de la empresa (por eso son Basic).
- Optional / AdminOnly: sí entran en authorize/revoke (circuito actual).

Entrada de menú vs solo enlace desde Mis empresas: también a reconsiderar en
ese change futuro (no cerrado en explore).

