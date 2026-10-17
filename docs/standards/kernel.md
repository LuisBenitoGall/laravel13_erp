# Kernel — invariantes innegociables

> Constitución always-on del ERP v2. Todo agente (10/20/30/40, explore, setup) las
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

- **Company** (dominio Partners) es el único tipo de actor organizativo. Todas las
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
- Sin empresa preferente hardcodeada (R-TEN-06). Cross-tenant = bug; cada entidad
  de negocio lleva test de aislamiento (R-TEN-05).

## 3. Super Admin

- Rol plataforma `Super Admin` (`company_id` NULL en Spatie Teams).
- `Gate::before`: bypass de **permisos, módulos y funcionalidades**.
- **No** bypass del perímetro de datos: solo ve/edita companies a las que está
  **vinculado**. No existe “ver todas las empresas” salvo change explícito.

## 4. Module ≠ Functionality (no confundir)

| Concepto | Qué es | Qué no es |
|---|---|---|
| **Module** | Unidad **SaaS-activable** (plan/catálogo). Gate de `EnsureModuleIsActive`. Catálogo **global** (excepción R-TEN-01, sin `company_id`). | La unidad de permiso ni de ítem de submenú. |
| **Functionality** | Unidad de **permiso y menú** dentro de un Module. Slug **inmutable** tras crearse. Catálogo global. | Un Module. |

Permisos Spatie: `<slug-de-Functionality>.<acción>` (`modules.viewAny`,
`invoices.create`). **No** usar el slug del Module como prefijo salvo el comodín
`<module-slug>.access`. Habilidades: `viewAny/view/create/update/delete` (nunca los
7 de v1 `index/create/show/search/edit/update/destroy`).

Al crear una Functionality se auto-provisionan esos 5 permisos (R-AUT-07).

## 5. Tres capas de módulos (R-AUT-08)

Un solo mecanismo de menú/acceso (prohibido replicar `basic-menu.json` /
`secondary-menu.json` de v1). Triple condición, **todas** obligatorias:

1. **Catálogo global** (`modules.status`): Active / Inactive. Inactive ⇒ invisible y
   bloqueo total de sus Functionalities en **todas** las empresas.
2. **Activación por empresa** (`company_modules`): la empresa enciende/apaga módulos
   a los que tiene derecho (permiso `company-modules.*` o Super Admin). Soft delete.
3. **Elegibilidad** (`company_module_authorizations`): Module Active ≠ la empresa
   puede activarlo. Solo Super Admin marca autorizado sí/no. Soft delete.
   - `ModuleLevel::Basic`: se autoriza por defecto al crear company activa y al
     activar un Module Basic.
   - `ModuleLevel::Optional`: requiere autorización Super Admin.
   - `ModuleLevel::AdminOnly`: **mismo circuito que Optional** (no auto-autorizar).
     No inventar “solo ciertas companies” u otras restricciones extra.

`MenuBuilder` y `EnsureModuleIsActive`: Active **y** autorizado **y** encendido para
la empresa de sesión; **después** comodín `<module-slug>.access` **OR** al menos un
`viewAny` de Functionality del módulo. El comodín **implica** el `viewAny` de todas
las Functionalities del módulo. Prohibido el AND estricto de v1 (exigir comodín
aunque el usuario ya tenga permiso de una Functionality).

Encender/apagar (Capa 2) **no** altera los permisos del rol; autorizar/desautorizar
(Capa 3) sí es el momento de sincronizar derecho a activar.

## 6. Referencia v1

Ruta: `C:\laragon\www\laravel8_erp_viudavila_github`. **Solo lectura funcional**
(reglas de negocio, modelos, flujos). **Prohibido** clonar su stack (Livewire,
Blade, yajra, middleware `*Get/*Post`, `session('currentCompany')`, menús
duplicados, `serialize()`, MySQL sin strict). Portar una regla de v1 exige
dejarla en spec/design; si no está escrita, no se “recuerda” del código viejo.

## 7. MCP y toolchain de agentes

| Servidor | Para qué | Dónde |
|---|---|---|
| **CodeGraph** | Símbolos, callers, impacto, traza. Preferir a grep estructural. | `.mcp.json` |
| **Laravel Boost** (`php artisan boost:mcp`) | API/docs Laravel vigentes. PHP de Laragon en Windows (PATH de Cursor no incluye `php`). | `.mcp.json` |
| **Tabularis** | Inspección MySQL. MCP de **usuario**, no de proyecto, hasta que exista conexión a la BD de **este** ERP. | No añadir la conexión Hetzner/`back` (otro sistema). |

Harness versionado en git: `.cursor/rules`, `skills`, `agents`, `commands` y
`.vscode/extensions.json` + `settings.json`. El resto de `.cursor/` / `.vscode/`
sigue ignorado (caché local).

## 8. Qué no decidir aquí

Cualquier semántica de negocio ausente de este documento, de `architecture.md` o
del change OpenSpec activo → opciones A/B y 10-architecture (o Luis). En
particular: planes/pago reales de módulos, “ver todas las companies” para Super
Admin, roles Spatie por empresa más allá de lo ya especificado, engorde de
Company al maestro v1.
