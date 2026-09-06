# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. **Como quedó** tras `c2026-07-17-admin-catalog-permissions`
> (Capa 1+2 Fase 0).

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Permission con Teams
(`company_id`); rol Super Admin + usuario de trabajo; visibilidad de menú R-AUT-08
(sin gate `CompanyModules` todavía); AppLayout + ServerTable + formularios mínimos;
login/logout de sesión.

**No entra (esta iteración):** `Company` / `CompanyModules` / roles por empresa /
`user_companies`; registro público ni reset de contraseña; otros catálogos globales
(currencies, countries…); resto de dominios de negocio.

## Entidades

| Entidad | Dominio | Notas |
|---|---|---|
| `Module` | `App\Domains\Admin` | `--global --status --translatable`; enums `ModuleLevel`, `ModuleStatus` |
| `Functionality` | `App\Domains\Admin` | `--global --status --translatable`; slug inmutable; FK `module_id` |

Infra transversal: Spatie Teams (`config/permission.php`), `Gate::before` Super Admin,
`MenuBuilder`, `AppLayout`, `ServerTable`, login sesión, `AdminCatalogSeeder`.

## Dependencias

- Kernel tooling: generador con `--global`, Support, Table*, arch ignoring globales.
- Frontend bootstrap: Inertia + React + TS + Tailwind.
- Paquetes: `spatie/laravel-permission` **^8** (Laravel 13; canon `libraries.md`),
  `spatie/laravel-translatable`, `tabulator-tables`, RHF + zod + Lucide.
- `module-map.md`: sin cambio de dependencias entre dominios.

## Checklist de vinculaciones (V-01..V-20)

| # | ¿? | Respuesta | Cómo quedó |
|---|---|---|---|
| V-01..V-06 | No | — | — |
| V-07 | Sí | Spatie Teams; seed Super Admin; CreateFunctionality provisiona 5 permisos + comodín | |
| V-08 | Sí | `MenuBuilder` → prop Inertia `menu`; excepción sin CompanyModules documentada | |
| V-09 | Sí (catálogo) | Seed Module `admin` + Functionalities `modules` / `functionalities` | |
| V-10..V-11 | No | — | — |
| V-12 | Sí | HasTranslations + forms con traducciones | |
| V-13..V-20 | No (esta iteración) | Activity log diferido | |

## Permisos (como quedó)

- Functionality: `{modules|functionalities}.{viewAny,view,create,update,delete}`
- Comodín: `admin.access`
- Rol **Super Admin** con `company_id` NULL; pivotes Spatie `company_id` nullable
- Credenciales: `config/superadmin.php` / env

## Rutas y UI

- `routes/domains/admin.php` (auth): CRUD Modules / Functionalities + `*/table`
- Login/logout en `routes/web.php`
- Pages: `Admin/Modules/*`, `Admin/Functionalities/*`, `Auth/Login`
- Shared Inertia: `auth`, `menu`, `flash`, `tenant.logo_url`

## Referencia v1 / migración

Sin cambio respecto a la ficha de propuesta: Policies (no middleware ad-hoc); menú
data-driven único; migración v1→v2 fuera de alcance.
