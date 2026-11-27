# Módulo Admin — catálogo, RBAC, shell, Company y Bank

> Ficha playbook §1. Incluye platform-roles, company-custom-roles, company-role-assignment,
> form-kit, platform-console-mvp, shell-navigation, `c2026-10-28-shell-topbar` y
> **`c2026-11-12-admin-sole-domain`** (absorbe Partners/Banks: único dominio PHP Admin,
> Functionalities bajo `/admin`).

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad + activación; menú R-AUT-08; roles de empresa `manager`/`guest`; sync de
permisos del manager; CRUD roles de plataforma y custom tenant; asignación User↔rol
local; kit Form MVP; consola plataforma MVP (tenants perímetro); **shell**: Dashboard
fijo, logos ERP+company, bloque Configuración SA (AdminOnly sin exigir
authorized∩enabled), bypass `EnsureModuleIsActive` solo AdminOnly+SA (perímetro de
datos intacto); **Company** (tenant / Mis Empresas); **Bank** (catálogo global).

**No entra:** facturación SaaS (Cashier/Stripe/cuotas); ver todas las companies sin
vínculo; invitar miembros no vinculados; engorde Company (CustomerProvider/obras…);
Wysiwyg/Dropzone; menú/Index/CRUD genérico de Funcionalidades; soft delete de módulos
shell (`configuracion` (+ Basics `dashboard`/`cuenta`/`usuarios`/`empresas`));
`BankAccount` / sync SWIFT automático.

## Entidades / infra RBAC

| Pieza | Notas |
|---|---|
| `Module` / `Functionality` | Catálogo global |
| `CompanyModuleAuthorization` / `CompanyModule` | Capas 3 y 2 |
| `Company::forUser` | Scope perímetro `user_companies` (SA inclusive) |
| Modules Basic `dashboard`/`cuenta`/`usuarios`/`empresas` | Menú PDF mínimo |
| Roles Spatie `manager` / `guest` | Plantilla universal + copia local |
| Roles plataforma / custom | Admin CRUD / company CRUD |
| Index roles plataforma | ServerTable (name, description, universal, Acciones Edit + Delete confirm si `can_delete`); Super Admin fuera; CTA Crear → `/settings/roles/create`; Create = solo metadatos (name, description, universal; `permissions` prohibited); Edit ancho completo estilo v1 (anclas Module + cards Functionality; plegar/desplegar todos; `ScrollToTop`); matriz = canónicos + custom (`{functionality}.*`, sin `*.access`); permisos por toggle async (`patchJson`) + auto `{module}.access`; Guardar = solo descripción; CTA Copiar (≥2 companies Active, no universales; destinos perímetro sin sesión; permisos = intersección origen ∩ catálogo tenant destino; versionado nombre); plantillas `manager`/`guest` no borrables pero matriz editable; materialización locales siempre `universal=false`; manager local sigue por sync |
| `AssignCompanyRole` | Cualquier rol local; un rol por company |
| Functionality `roles` / `company-roles` | Plataforma / Module `usuarios` |
| Create roles empresa | Metadatos name+description (`RoleMetadataFields` sin universal); sin matriz; authz `company-roles.create`; POST → `company_id` sesión + `universal=false` → redirect Edit; CTA Index → `/users/company-roles/create` |
| Edit roles empresa | Espejo Edit plataforma: layout anclas/cards; name readonly; Guardar = solo descripción; matriz = catálogo autorizados∩activos∩¬AdminOnly (canónicos+custom); toggles async `patchJson` + `{module}.access`; `manager` local `permissions_readonly`; Index con `company-roles.view` (sin exigir manager; delete sí); sin copy tenant; `can_edit=!universal`; universales bloqueados en edit/toggle; Actions refuerzan perímetro CurrentCompany |
| Consola plataforma | Companies Index (CTA) + authorizations scoped |
| Shell navegación | Dashboard + Configuración SA; seed ACME; post-login → dashboard |
| Menú pilares | Empresas (Mis empresas → `/companies`; **Delegaciones** → `/companies/delegations`, Functionality `delegations`; **Directorio** → `/companies/company-directory` grid+aside sectores OR, Active+NIF+≥1 sector, `company-directory.view`; excepción ServerTable; **Centros de trabajo** → `/companies/workplaces` ServerTable, Functionality `workplaces`; **Configuración de la empresa** → `/companies/company-settings` hub onboarding, Functionality `company-settings`), Usuarios (Usuarios empresa→`/users/company-users` ServerTable `user_companies`×sesión; Todos los usuarios→`/users/all-users` directorio `is_public=true` + máscara notificación; `company-users.*` / `all-users.view` (Index; `.viewAny` = detalle); Members legacy sigue para asignación de rol), Configuración SA (Módulos/Roles/Permisos/Países/Monedas/**Conversión monedas**/Bancos/Unidades/Sectores/Métodos de pago/Tipos de IVA/Grupos contables/**Contenidos**/**Galería**/**Cuentas**; sin ítem genérico Funcionalidades). Bancos: entidad Admin |
| Contenidos (Content) | Kinds Help\|Article; Create: name+kind; **Help exige `code` explícito** `{scope}.{field}` (sin slugify); Article → auto-slug; Inactive; Edit real (`RecordSummary`+form; kind/code inmutables; Eliminar); `company_id` null; soft delete; **sin** BelongsToCompany; `contents.*`; Index toggle→Edit→Delete; HelpTip + `FormHelpScope`/`Field` auto `{scope}.{field}` (NIF `companies.nif`); **Fase 2:** Wysiwyg Article inserta `<img>` desde Galería (`enableGalleryInsert`; no Help); **video opcional** en update (Help y Article; `[]` válido); fixture+seeder+`contents:export-help`; published_* = futuros. V-07/V-08 sí; R-TEN-05 N/A; E2E N/A |
| Galería (MediaAsset) | Functionality `gallery` bajo `configuracion` (tras Contenidos); `/settings/gallery`; grid 6 cols (excepción ServerTable); Index carga completa (sin paginar; revisable); UI gated `can.create/update/delete`; multi-upload + name+alt+URL; crop/resize cliente (`react-easy-crop`, pan+zoom; ayuda + ratios) → **copia**; **Fase 2/2b/3:** `GET admin/gallery/picker` JSON (`q`/`page`/`per_page`, `{assets,meta}`) + `Form/MediaPicker` reutilizable; único consumidor actual = Wysiwyg Article (`enableGalleryInsert`); `company_id` nullable (altas/copias plataforma null; upload empresa bloqueado); soft delete (+ borra fichero); **sin** BelongsToCompany; `gallery.*`; ≠ UserImage. V-07/V-08/V-21 sí; R-TEN-05 sí (no-null); E2E N/A |
| Cuenta vs ficha user | Module `cuenta`: Mi cuenta (slug Functionality `profile`) → `/account` histórico + CTA renovar/mejorar (no ficha User); renovación `/account-renew` + `AccountCatalogGrid`; Mis módulos → company-modules. Ficha self `/account/profile*` solo desde avatar topbar; AsideNav sin highlight en `/account/profile*`; menú lateral sin href `/account/profile` |
| CompanyAccount | Vínculo SaaS Company↔Account; `BelongsToCompany`; status Pending/Active/Expired/Cancelled; ≤1 Active; alta vía `CreateCompany`→`AssignDefaultCompanyAccount`; renovación `RenewCompanyAccount` (`profile.update`); job `company-accounts:expire` diario; middleware gate 403+CTA → `admin.account.renew`; whitelist histórico/renew/select/logout; `admin.accounts.renewal` → redirect `admin.account.renew`. V-16/V-18/V-20/V-21; R-TEN-05 sí; E2E N/A |
| Company (Mis Empresas) | Tenant sin `BelongsToCompany`. Index ServerTable; Create name/tradename/nif/status (sin jerarquía ni sectores ni excerpt/description ni monedas); slug auto; Edit tabs Info/Imágenes/**Jerarquía**/Sectores; tab Info = identidad + `excerpt`/`description` multi-idioma (`LangTabs`; Textarea ≤1000; `Form/Wysiwyg` TipTap; HTML allowlist al guardar) + **sección Monedas** (pivote `company_currencies`: N permitidas + 1 default; sync propio `PUT …/currencies`; `companies.update`; sin tab Monedas); tab Sectores = N:N Active (`company-sectors.view` ver / `.update` sync; sin menú lateral); `parent_id` nullable (1 padre o ninguno, N niveles, anti-ciclo; solo vínculo `user_companies`); logo POST `admin.companies.logo`; rutas `/companies`; al crear: roles manager/guest + vínculo creador + `company_accounts` Active. V-18/V-20 sí (readiness + Auditable sync); V-07/V-08 no (sin Functionality monedas); R-TEN-05 sí; E2E N/A |
| Delegaciones | Functionality `delegations` bajo Module Basic `empresas`; CRUD `/companies/delegations*` (Index ServerTable hijas de sesión; Create/Edit distintos de Mis empresas); Delegación = Company con `parent_id` = matriz de sesión; alta: NIF + ≥1 Workplace **de la matriz** vinculado (nuevo y/o existentes vía pivote; **sin** `company_id` = hija; **sin** monedas exigidas); Edit Info = identidad + **sección Monedas** propia; tab Centros = `<ServerTable>` sedes vinculadas (geo/fecha + **sede preferida** `is_preferred_seat` ≤1 opcional; sin columna `is_preferred`; Edit row → Workplace; vacío → copy de ayuda hacia Centros, sin CTA botón); **sin** `company_accounts`; switcher/select/login solo matrices; sesión no matriz → CRUD rechazado; V-07/V-08 no nuevos (sin Functionality sede); V-21 sí; R-TEN-05 perímetro matriz; E2E N/A |
| Directorio empresas | Functionality `company-directory` bajo `empresas`; Index `/companies/company-directory` (aside sectores Active OR + grid cards; sort name/recientes; paginación; **sin** ServerTable); Show informativo sin CTA contactar/cliente-proveedor; universo Active+NIF+≥1 sector; Policy `viewAnyCompanyDirectory` ≠ `forUser` Mis empresas |
| Workplace (Centros de trabajo) | `BelongsToCompany`; CRUD `/companies/workplaces` ServerTable + Create/Edit (único alta de sedes del grupo = matriz); dirección embebida `address`/`cp`/`town_id`; `featured` UI **Principal** ≤1/company; status Active/Inactive; Edit: mapeo N:N a Delegaciones + preferida-por-sede ≤1 (`workplace_delegation.is_preferred`, `workplaces.update`); pivote también `is_preferred_seat` (sede preferida por Delegación; escritura en Edit Delegación); sedes operativas **no** viven con `company_id` = Delegación; Functionality `workplaces` bajo `empresas`; ≠ Worksite/Workspace; V-07/V-08; R-TEN-05 |
| Configuración de la empresa (hub) | Functionality `company-settings` bajo `empresas`; Index `/companies/company-settings` (progreso + tabs Identidad\|Sedes\|**Monedas**; `?wizard=1` asistente); readiness = nif+logo+≥1 workplace Active+**moneda por defecto** (`total_steps`=3); sync monedas `PUT company-settings/currencies` (sesión); post-`CreateCompany` (onboarding o Mis empresas) session `company_setup.show_modal` → modal CTA hub + «Ahora no» (dismiss→dashboard); banner shell mientras incomplete; soft-gates sin muro. V-07/V-08/V-18 sí; E2E N/A |
| Sectores (Sector) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive; name JSON traducible (Spatie); Index ServerTable (name; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create/Edit con LangTabs; Active-first+name; Functionality `sectors` + `sectors.*`; Configuración→Sectores; pivot `company_sector`; `SectorsSeeder` **19 secciones NACE A–S** (ES/EN/CA; sin T/U; upsert; no borra slugs legacy); Index no importa. V-07/V-08 sí; E2E N/A |
| Métodos de pago (PaymentMethod) | Catálogo global plano (sin `company_id`/code/`accounting_account_id`); soft delete; status active/inactive; name JSON traducible (Spatie); Index ServerTable (name; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create/Edit con LangTabs; Active-first+name; Functionality `payment-methods` + `payment-methods.*`; Configuración→Métodos de pago; sin pivot empresa; `PaymentMethodsSeeder` **12 ids 1–12** (ES/EN/CA; Active; upsert por id; no borra ajenos; `php artisan db:seed --class=PaymentMethodsSeeder`; no en DatabaseSeeder; Index no importa). V-07/V-08 sí; E2E N/A |
| Bank | Catálogo global (sin `company_id`); softDeletes; Functionality `banks` + Spatie `banks.*`; Index Configuración→Bancos; SWIFT pad 11; `eu_code` unique |
| Index Usuarios empresa | ServerTable name/surname/email; toggle `users.status`; eliminar = detach `user_companies` (no borra User); CTA Crear → `/users/company-users/create`; fila Edit → ficha `Users/Edit`; vista rápida (ojo+nombre → Sheet + `UserProfileReadout`, Editar si `update`) |
| Create Usuarios empresa | Alta/vínculo `CreateOrLinkCompanyUser`; email required; `is_admin=true`; `is_public` (default true); password solo si «enviar credenciales»; rol = universales ∪ locales (Super Admin solo si actor SA); mails = máscara flash; POST → Edit; authz `company-users.create` |
| Edit User unificado | Misma página Inertia `Users/Edit` vía `/account/profile` (self, breadcrumb `perfil` ≠ menú Mi cuenta) y `/users/company-users/{user}/edit` (ajeno); `RecordEditLayout` + `RecordSummary` + tabs Perfil/Contraseña/Emails-tels/Imágenes/Documentos/Empresas/**Empleos** (RO multi-company si ≥1 periodo; R-TEN-10; sin `employees.view`); `UserProfileReadout` RO (avatar/nombre/email; emails+copiar sin observación; tels; empresas vinculadas si hay; sin NIF/fecha/activo/público); Contraseña solo self (ni SA); `is_public`/`status` vía `UserProfileAuthorization` (self o permiso TBD); authz ajeno `company-users.update` + perímetro |
| User companies (membership) | Tab Empresas: ServerTable de `UserCompany` (todas las companies del user); cols nombre/posición/departamento/fecha alta; edit modal posición/departamento; delete = detach vínculo (no borra User/Company); última company OK; self detach de sesión → otra company (menor id) o logout; sin CTA alta; Policy viewAny/update/delete = self o `updateCompanyUser`; rutas `admin.profile.companies.*` / `admin.company-users.companies.*`; ≠ Empleo |
| User emails adicionales | Tab contactos: col. emails (`UserEmail` N/user; cards + modal alta; mailto/copiar/eliminar; sin edit); `user_emails.email` unique global; ≠ principal; Policy create/delete = self o `updateCompanyUser`; Action + perímetro company; rutas `admin.profile.emails.*` / `admin.company-users.emails.*` |
| User phones | Tab contactos col. teléfonos: tabla polimórfica `phones` (`Phone` morph; UI/API solo User); cards + modal alta/edición; tel/copy/WhatsApp/primario/edit/delete; badge verificación solo informativo; `country_id` null; unique owner+e164; Policy create/update/delete/markPrimary = self o `updateCompanyUser`; Actions + perímetro; rutas `admin.profile.phones.*` / `admin.company-users.phones.*` |
| User images | Tab Imágenes (tras emails/tels): `UserImage` + disco `public` `users/{id}/images/…`; Dropzone kit + grid/lightbox; featured=avatar readout/topbar; toggle `public` (visible público solo si `user.is_public`); límites 20/1MB jpg|png|gif|webp; Policy upload/delete/featured/public = self o `updateCompanyUser`; soft delete borra fichero; rutas `admin.profile.images.*` / `admin.company-users.images.*` |
| User docs | Tab Documentos (tras Imágenes): `UserDoc` personal (no company) + disco `local` privado `users/{id}/docs/…`; Dropzone pdf/doc/docx/xls/xlsx 20 MB; máx 30/user; listado download authz + delete; Policy upload/download/delete = self o `updateCompanyUser`; soft delete borra fichero; sin share; rutas `admin.profile.docs.*` / `admin.company-users.docs.*`; ops PHP ≥20 MB |
| Index Todos los usuarios | ServerTable global filtrado `is_public=true`; sola acción máscara notificación; sin edit/toggle/delete; vista rápida RO (ojo+nombre → Sheet, sin Editar) |
| Unidades (Unit) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive; Index ServerTable (name, symbol; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create/Edit; symbol max 4; name unique; Functionality `units` + `units.*`; Configuración→Unidades; `UnitsSeeder` datos v1 (IDs/status preservados; sin translations); Index no importa. V-07/V-08 sí; E2E N/A |
| Cuentas SaaS (Account) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive; name/slug/description/rate/duration/`is_default`; Index ServerTable (name, rate, duration, default; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create/Edit; **una sola default**; delete de la default bloqueado; Functionality `accounts` + `accounts.*`; Configuración→Cuentas; `/settings/accounts/renewal` → redirect `/account-renew`; `AccountsSeeder` ≥1 Active default (`php artisan db:seed --class=AccountsSeeder`; no en DatabaseSeeder; Index no importa). ≠ Module `cuenta`, ≠ AccountingAccount. V-07/V-08/V-20/V-21 sí; R-TEN-05 N/A; E2E N/A |
| Tipos de IVA (VatType) | Catálogo global por Country (sin `company_id`); FK `country_id` required (selector todos los países); rate decimal(5,2); RE nullable; soft delete; Index plano con columna País+filtro, name, rate, RE; Acciones toggle→Edit→Delete; modales; Active-first+name; Functionality `vat-types` + `vat-types.*`; `VatTypesSeeder` → España id=66 (tras GeographySeeder); Index no importa. V-07/V-08 sí; E2E N/A |
| Grupos contables (AccountingAccountType) | Catálogo jerárquico PGC (sin `company_id`/status); autoreference/code/name/mode; soft delete; Index ServerTable (code, name, level); Create modal (raíz); Edit solo name (code inmutable); Delete bloqueado si hijos; Show árbol desde BD; Functionality `accounting-account-types`; `AccountingAccountTypesSeeder` + `lang/*/fiscal.php`; V-07/V-08 sí; E2E N/A |
| Geografía (Países) | Catálogo global Country→Province→Town (sin `company_id`); soft delete; status active/inactive; Index drill v1+ServerTable; Functionality `countries`; cascada JSON; seeder datos v1 con IDs preservados; `flag` nullable; Indexes sin columna Estado (toggle en Acciones); drill Map/MapPin ≠ Pencil |
| Monedas (Currency) | Catálogo global plano (sin `company_id`); soft delete; status active/inactive (enum `CurrencyStatus`); Index ServerTable (name, code, number, symbol; sin columna Estado) + Acciones toggle→Edit→Delete; modales Create (name, code, number, symbol, status) / Edit (solo name+symbol; `code`/`number` inmutables); symbol max 8; Functionality `currencies` + `currencies.*`; Configuración→Monedas (SA); `CurrenciesSeeder` datos v1 con IDs preservados (EUR=2, USD=5, GBP=67, CHF=95; Active solo EUR/USD/GBP/CHF); Index no importa (sin efecto lateral en GET). Vinculaciones: V-07 sí, V-08 sí, V-09 N/A; E2E N/A |
| Conversión monedas (ExchangeRate) | Catálogo global de cotizaciones (sin `company_id`); soft delete; sin status Active/Inactive; par dirigido `from`→`to` + `rate` decimal(18,6); **sin** `rate_date`; unique (from,to); un par unordered una sola vez; Index ServerTable (par, A→B, B→A) + Acciones Edit→Delete ambos sentidos; Create/Edit dual (CTA rellenar inverso editable); vigente = rate actual (`ResolveExchangeRate`); norma snapshot en docs futuros (aplazada); Functionality `exchange-rates` + `exchange-rates.*`; Configuración→Conversión monedas (tras Monedas); sin seeder/API. V-07/V-08/V-20/V-21 sí; R-TEN-05 N/A; E2E N/A. Changes `…-conversion-monedas` + `…-par-unico` |
| Catálogo Permisos | Index ServerTable (name, Functionality vía prefijo, Acciones Delete custom); Create modal (`{slug}.{sufijo}` único; reserved 5+access); canónicos + `*.access` no borrables; Functionality `permissions` + Policy; sin Edit ni asignación Roles/Users |
| Shell topbar | Bloque 1: collapse izq. / acciones der.; notificaciones/chat stub popover; **notas** = Dialog real `Note`; **favoritos** = popover real `Preference`; perfil+logout |
| Preferencias / favoritos | Modelo `Preference` (`BelongsToCompany` + `user_id`; url path sin host, **≤760** por índice unique utf8mb4 MySQL; solo prefijos `/admin` `/dashboard` `/account`; rechaza `//` protocol-relative; `module_id` nullable vía longest-prefix + `MenuFunctionalityHref`); POST/DELETE `/admin/preferences`; Dashboard CTAs color/icon Module + delete; tope 20/(user,company); sin Functionality menú. V-07 Policy sí / V-08 no; R-TEN-05; E2E N/A |
| Notas internas | Modelo `Note` (solo `user_id`; sin `company_id`/`status`); POST/DELETE `/admin/notes`; modal topbar + badge `notes_count`; post-its dashboard; tope 50/user; V-07 Policy sí / V-08 no; E2E N/A |
| Shell topbar polish | Mini-rail md+ (`erp.aside.collapsed`); móvil off-canvas; flyout B1; logos reducidos; notas=Dialog (excepción vs popover stub) |
| Shell breadcrumb | Bloque 2: `PageBreadcrumb` option (Link) + suboption; Index → `dashboard` |
| Shell action bar | Bloque 3: `ViewActionBar` (Volver con `backHref` fijo o origen, CTAs, delete+confirm); ≠ FormActions / Records/ActionBar |
| Listado Modules | Referencia `ServerTable`: name, color, icon (Lucide + `title`), level (i18n), created_at y Acciones (toggle solo `optional` → Edit → Delete confirm en no-shell; sin Delete en shell; sin columna Estado ni búsqueda avanzada). Inactive permitido también en shell vía UpdateModule. Export xlsx/pdf por el mismo `table` |
| Create Module | Alta con LangTabs (label+explanation; principal required), IconPicker, ColorPicker, Switch status default Active; slug solo servidor + `{slug}.access`; redirect Edit. Kit: `Form/LangTabs`, `ColorPicker`, `IconPicker` |
| Edit Module | Tabs Módulo \| Funcionalidades; form kit; slug inmutable/oculto; level readonly i18n; `backHref=/settings/modules`; RecordSummary; ActionBar Nueva funcionalidad + Delete (no shell) |
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

## Vinculaciones — contents-create (c2026-11-16-contents-create)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality `contents` + Spatie `contents.*` |
| V-08 | Sí | Configuración → Contenidos |
| V-09 | No | Module `configuracion` existente |
| R-TEN-05 | N/A | Alta `company_id` null; HelpTip no depende de company sesión |
| E2E | N/A | `e2e.md` §4; Features Create/Index/Edit/HelpTip |

## Vinculaciones — contents-video-optional (c2026-11-20-contents-video-optional)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-20 | No | Solo validación Update; sin menú/permisos/módulo |
| E2E | N/A | Feature HTTP |

## Vinculaciones — admin-gallery (c2026-11-20-admin-gallery)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality `gallery` + Spatie `gallery.*` |
| V-08 | Sí | Configuración → Galería (tras Contenidos) |
| V-09 | No | Module `configuracion` existente |
| V-21 | Sí | Rutas `/settings/gallery*` bajo configuracion |
| R-TEN-05 | Sí | Activos con `company_id` set; altas Fase 1 solo null |
| E2E | N/A | `e2e.md` §4; Feature HTTP |

## Vinculaciones — admin-gallery-wysiwyg (c2026-11-20-admin-gallery-wysiwyg)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-06 | No | Sin stock/ledger/numeración/doc |
| V-07/V-08 | No | Sin Functionality/menú nuevos (reusa `gallery.*` / `contents.*`) |
| V-09..V-20 | No | Sin módulo/seeder/policy nuevos |
| V-21 | No | Ruta picker bajo `gallery` ya existente |
| R-TEN-05 | Sí (picker JSON) | Aislamiento listado; Feature en GalleryTest |
| E2E | N/A | design D7; Feature + unit sanitizer |

## Vinculaciones — admin-gallery-picker-search (c2026-11-20-admin-gallery-picker-search)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-20 | No | Sin menú/permisos/módulo/seeder nuevos (Fase 2b) |
| R-TEN-05 | Sí (picker) | Perímetro sin cambio; Feature aislamiento + meta |
| E2E | N/A | design D6; Feature + assert fuente modal |

## Vinculaciones — admin-gallery-media-picker (c2026-11-21-admin-gallery-media-picker)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01..V-20 | No | Solo extracción front `Form/MediaPicker` (Fase 3 A) |
| E2E | N/A | design D6; assert fuente + Features picker/Content |

## Vinculaciones — admin-accounts (c2026-11-21-admin-accounts)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality `accounts` + Spatie `accounts.*` |
| V-08 | Sí | Configuración → Cuentas (tras Galería) |
| V-09 | No | Reusa Module `configuracion` |
| V-12 | No | name/description string como Unit |
| V-15 | No | Seeder mínimo (no dump v1 masivo) |
| V-16 | No | Job caducidad = change siguiente |
| V-18 | Sí | CreateCompany → AssignDefaultCompanyAccount |
| V-20 | Sí | Auditable como Unit |
| V-21 | Sí | Rutas `/settings/accounts*` bajo `module:configuracion` |
| R-TEN-05 | N/A | Catálogo global; test visibilidad global |
| E2E | N/A | design D8; Feature basta |

## Vinculaciones — admin-company-accounts (c2026-11-21-admin-company-accounts)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | No | Reusa `profile.*` (GET view / POST renew update) |
| V-08 | No | Misma entrada Mi cuenta |
| V-16 | Sí | `company-accounts:expire` diario |
| V-18 | Sí | `CreateCompany` → `AssignDefaultCompanyAccount` |
| V-20 | Sí | Auditable CompanyAccount |
| V-21 | Sí | `/account*` bajo `module:cuenta` |
| R-TEN-05 | Sí | CompanyAccount tenant |
| E2E | N/A | design D10; Feature gate + renew |

## Vinculaciones — admin-account-renew (c2026-11-22-admin-account-renew)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | No | Reusa `profile.*` |
| V-08 | Sí | href Mi cuenta → `/account` |
| V-16/V-18/V-20 | No | Sin cambio |
| V-21 | Sí | Rutas bajo `module:cuenta` + prefijo admin |
| E2E | N/A | Feature MenuCuenta + CompanyAccount |

## Dependencias

Sin dependencia a otros dominios PHP en Fase 0: Company y Bank viven en Admin.
Lecturas cross-domain futuras: ver `module-map.md`.


## Menú PDF (backlog)

Mapa MENU_ERP = referencia de trabajo ordenado (1 change ≥ Module; ≥1 por Functionality al abrirla). No sembrar Optional vacíos del PDF en este change.

## Pendiente: reformular company-module-authorizations

Explorado 2026-11-05 (depuración menú). Por ahora se deja la Functionality/ruta
bajo Configuración; **habrá que reformular** la UX/contrato.

Regla ya acordada (Luis), pendiente de change:

- En la UI de elegibilidad (`/settings/company-module-authorizations`) **no** deben
  aparecer Modules `Basic`: no se autorizan ni se revocan; están siempre a
  disposición de la empresa (por eso son Basic).
- Optional / AdminOnly: sí entran en authorize/revoke (circuito actual).

Entrada de menú vs solo enlace desde Mis empresas: también a reconsiderar en
ese change futuro (no cerrado en explore).

