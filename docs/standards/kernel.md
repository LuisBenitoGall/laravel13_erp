# Kernel — invariantes innegociables

> Constitución always-on del ERP v2. Todo agente (10/20/25/30/40, explore, setup) las
> cumple **sin que el usuario las recuerde**. El detalle citable son las reglas `R-*`
> de [architecture.md](architecture.md). Si kernel y código chocan: se corrige el
> estándar o el código en un change explícito; **nunca se improvisan excepciones**.
> Si una decisión no está aquí ni en architecture/vision/spec: **no inventar**.

## 1. Stack (no negociable)

| Capa | Es | No es |
|---|---|---|
| Backend | Laravel 13 / PHP 8.3+ (enums, match, promoted properties, readonly) | Laravel 9/10 de v1 |
| UI | **Inertia v3 + React 19 + TypeScript estricto + Tailwind 4** (shadcn/ui) | **Livewire, Blade CRUD, jQuery, Bootstrap/Inspinia** |
| Listados | `<ServerTable>` (Tabulator) contra `*/table` | yajra/DataTables, TanStack como estándar |
| Tenancy | BD única + `BelongsToCompany` + `CurrentCompany` | `stancl/tenancy`, `session('currentCompany')` |
| Tests | Pest 4 (+ arch + **browser** = Playwright bajo Pest) | Suite Playwright TypeScript aparte |
| Authz | Policy por modelo + Spatie Teams | Middleware `*Get/*Post` de v1 |

Dependencias solo del canon [libraries.md](libraries.md). UI en español vía i18n;
identificadores en inglés según [glossary.md](glossary.md).

## 2. User y Company

- **Company** (dominio Admin) es el único tipo de actor organizativo. Todas las
  empresas son iguales (SaaS). Un autónomo es User + Company, no un tercer tipo.
  Company **es el tenant**: no lleva `BelongsToCompany`.
- **User** puede existir **sin** empresa (contactos, leads…). **No entra al ERP**
  sin ≥1 company **activa** vinculada (`user_companies`). Soft delete: `companies` sí;
  `user_companies` no.
- Post-login: 1 empresa activa → sesión automática; N empresas → selección
  **bloqueante** antes de usar el ERP.
- Empresa activa: UNA vez por request en `SetCurrentCompany`, acceso solo vía
  `CurrentCompany`. Clave de sesión permitida **solo en ese middleware**:
  `current_company_id`. **Prohibido** `session('currentCompany')` en queries,
  modelos, Actions o vistas (R-TEN-03).
- Jobs/comandos/listeners: `company_id` explícito; restaurar `CurrentCompany`.
- Sinempresa preferente hardcodeada (R-TEN-06). Cross-tenant = bug en
  mutaciones y listados tenant-scoped; cada entidad de negocio lleva test de
  aislamiento (R-TEN-05). **Excepción (R-TEN-10):** consultas de **solo
  lectura** ancladas a un User (u otro hub declarado en el change/spec) que
  proyecten filas de varias companies del **sujeto** están permitidas cuando el
  change/spec lo declare. No equivale a “SA ve todas las empresas”.

### Grupo societario (matriz / Delegación / Workplace)

Todas las filas de `companies` son el mismo tipo organizativo (Company); **no**
todas son elegibles como empresa de sesión SaaS. Detalle R-TEN-11.

1. **Matriz**: Company con `parent_id` null. Es la empresa de sesión SaaS;
   aparece en el switcher / select de empresa; el alta genérica (Mis empresas)
   crea `company_accounts`.
2. **Delegación**: Company con `parent_id` = matriz. Tiene NIF y el mismo “ser
   empresa” en datos; **no** aparece en el switcher ni se elige como
   `CurrentCompany` por ese dropdown. Alta vía CRUD Delegaciones **sin**
   `company_accounts`. El gate SaaS / cuenta (`company_accounts`,
   `EnsureCompanyAccountIsActive`) se ancla a la **matriz de sesión**, no a la
   hija.
3. **Workplace**: pertenece siempre a la **matriz** (`company_id` = matriz).
   Se asocia N:N a Delegaciones hijas vía `workplace_delegation`, con como
   máximo una **preferida** (opcional) por workplace. Alta/edición de sedes
   solo en Centros de trabajo (sesión matriz); escritura del vínculo en Edit
   Workplace. **SHALL NOT** persistir workplaces con `company_id` = Delegación.
   Ver [glossary.md](glossary.md) (Delegación, mapa sede–delegación).

### Invariantes aplazadas — documentos comerciales

> **SHALL al implementar Sales/Orders (y albaranes/facturas relacionados).
> No se implementa en el change de constitución del grupo.** Texto citable
> para no reabrir el debate al llegar a presupuestos/pedidos.

- En presupuesto/pedido de **venta**, “nosotros” = `orders.provider_id`; en
  **compra**, “nosotros” = `orders.customer_id` (ambas FK a `companies.id`).
- Default = matriz de sesión. UI con dropdown de Delegaciones hijas; si el
  documento lleva workplace con mapeo, default = la preferida de esa sede
  cuando exista.
- El usuario puede cambiar la sociedad hasta **antes** de facturar; tras
  facturar queda bloqueada.
- Albarán hereda la sociedad del pedido.
- Listados desde la matriz: por defecto ven documentos propios **y** de hijas;
  filtro multi-delegación.

## 3. Super Admin

- Rol plataforma `Super Admin` (`company_id` NULL en Spatie Teams).
- `Gate::before`: bypass de **permisos, módulos y funcionalidades**.
- **No** bypass del perímetro de datos: solo ve/edita companies a las que está
  **vinculado**. No existe “ver todas las empresas” salvo change explícito.
  Una proyección R-TEN-10 anclada a User **no** es ese bypass.

## 4. Module ≠ Functionality (no confundir)

| Concepto | Qué es | Qué no es |
|---|---|---|
| **Module** | Unidad **SaaS-activable** (plan/catálogo). Gate de `EnsureModuleIsActive`. Catálogo **global** (excepción R-TEN-01, sin `company_id`). Puede ser **hoja de menú** si no tiene Functionalities (p. ej. `dashboard`). | La unidad de permiso de acciones CRUD. |
| **Functionality** | Unidad de **permiso y subítem de menú** dentro de un Module. Slug **inmutable** tras crearse. Catálogo global. | Un Module. |

Permisos Spatie: `<slug-de-Functionality>.<acción>` (`modules.view`,
`invoices.create`). **No** usar el slug del Module como prefijo salvo el comodín
`<module-slug>.access`. Habilidades canónicas en BD:
`viewAny/view/create/update/delete` (nunca los 7 de v1
`index/create/show/search/edit/update/destroy`).

**Semántica producto (≠ Laravel por defecto):**

| Sufijo Spatie | Uso en el ERP |
|---|---|
| `{functionality}.view` | Dashboard / Index / menú (con comodín Module) |
| `{functionality}.viewAny` | Detalle (show) de un registro |
| `create` / `update` / `delete` | Sin cambio |

Policy Laravel (métodos PHP sin renombrar): `viewAny()` comprueba Spatie `.view`;
`view()` comprueba Spatie `.viewAny`.

Al crear una Functionality se auto-provisionan esos 5 permisos (R-AUT-07).

## 5. Tres capas de módulos (R-AUT-08)

Un solo mecanismo de menú/acceso (prohibido replicar `basic-menu.json` /
`secondary-menu.json` de v1). Las capas son:

1. **Catálogo global** (`modules.status`): Active / Inactive. Inactive ⇒ invisible y
   bloqueo total de sus Functionalities en **todas** las empresas.
2. **Elegibilidad** (`company_module_authorizations`): Module Active ≠ la empresa
   puede usarlo / encenderlo. Solo Super Admin marca autorizado sí/no. Soft delete.
   - `ModuleLevel::Basic`: se autoriza por defecto al crear company activa y al
     activar un Module Basic.
   - `ModuleLevel::Optional`: requiere autorización Super Admin.
   - `ModuleLevel::AdminOnly`: **mismo circuito que Optional** (no auto-autorizar).
     No inventar “solo ciertas companies” u otras restricciones extra.
3. **Activación por empresa** (`company_modules`): **solo Modules Optional**.
   Mis módulos (`/admin/company-modules`) lista únicamente Optional + Active global;
   Encender/Apagar rechaza Basic y AdminOnly. Soft delete. Basic Active y
   autorizados están **siempre disponibles** para menú/acceso de la company
   **sin** fila en `company_modules` y **sin** toggle en Mis módulos. AdminOnly
   **no** se gestiona en Mis módulos (disponibilidad Super Admin vía menú SA).

`MenuBuilder` y acceso a Functionalities:

- **Optional:** Active **y** autorizado **y** encendido.
- **Basic:** Active **y** autorizado (sin exigir `company_modules`).
- **AdminOnly + Super Admin:** excepción SA (sin authorize/enable en company).

**Después** modelo **AND** Spatie: comodín `<module-slug>.access` **y** el permiso
de dashboard `{functionality}.view`. Module hoja (sin Functionalities): basta el
comodín. El comodín **ya no implica** todas las Functionalities del módulo. Super
Admin: bypass Spatie intacto; perímetro company intacto. Mis módulos de SA = solo
company de sesión vinculada (no selector de empresas ajenas).

Encender/apagar Optional **no** altera los permisos del rol; autorizar/desautorizar
sí es el momento de sincronizar derecho a activar/usar.

## 6. Referencia v1

Ruta: `C:\laragon\www\laravel8_erp_viudavila`. **Solo lectura funcional**
(reglas de negocio, modelos, flujos). **Prohibido** clonar su stack (Livewire,
Blade, yajra, middleware `*Get/*Post`, `session('currentCompany')`, menús
duplicados, `serialize()`, MySQL sin strict). Portar una regla de v1 exige
dejarla en spec/design; si no está escrita, no se “recuerda” del código viejo.

## 7. MCP y toolchain de agentes

Jerarquía de contexto: este kernel + `R-*` > artefactos OpenSpec > Boost
(guidelines/skills/MCP) > skills genéricos. Boost **no** decide tenancy,
Module/Functionality ni menú.

| Servidor | Para qué | Dónde |
|---|---|---|
| **CodeGraph** | Símbolos, callers, impacto, traza de **nuestro** código. Preferir a grep estructural. | `.mcp.json` |
| **Laravel Boost** (`php artisan boost:mcp`) | Docs versionadas (Laravel 13, Inertia, Pest 4, Tailwind 4) y runtime **local**: `Search Docs`, `Application Info`, `Database Schema`/`Query`, logs. PHP de Laragon en Windows. Detalle: `.cursor/rules/laravel-boost.mdc`. | `.mcp.json` |
| **Tabularis** | Inspección MySQL de usuario (SQL ad-hoc / otras BDs). No sustituye Boost para el esquema de **esta** app. | No añadir la conexión Hetzner/`back` (otro sistema). |

`Database Query` solo contra `.env` local / test. `Record Rule` no crea invariantes
de negocio (eso es este documento o un change OpenSpec). `.ai/rules` no es
constitución.

Harness versionado en git: `.cursor/rules`, `skills`, `agents`, `commands` y
`.vscode/extensions.json` + `settings.json`. El resto de `.cursor/` / `.vscode/`
sigue ignorado (caché local).

## 8. Qué no decidir aquí

Cualquier semántica de negocio ausente de este documento, de `architecture.md` o
del change OpenSpec activo → opciones A/B y 10-architecture (o Luis). En
particular: planes/pago reales de módulos, “ver todas las companies” para Super
Admin, roles Spatie por empresa más allá de lo ya especificado, engorde de
Company al maestro v1.
