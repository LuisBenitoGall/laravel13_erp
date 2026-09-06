## Why

Sin catálogo `Module`/`Functionality`, RBAC real ni shell autenticado, el ERP no puede
gestionar permisos ni navegar como producto. El kernel (generador + Support) ya está
verde: toca la Capa 1+2 de Fase 0 — primeros catálogos globales con `--global`, Spatie
Teams, menú corregido respecto a v1, AppLayout/ServerTable y login mínimo para un
superadmin de trabajo.

## What Changes

- Extender `make:domain-entity` con `--global` (omitir tenancy; test de visibilidad
  compartida en lugar de R-TEN-05).
- Generar e implementar `Module` y `Functionality` (Admin, globales, status +
  translatable); auto-provisión real de 5 permisos al crear Functionality (R-AUT-07).
- Instalar/configurar `spatie/laravel-permission` con Teams (`company_id`); seed rol
  **Super Admin** (`company_id` NULL) + `Gate::before` (R-AUT-03); seed usuario
  superadmin (credenciales por config/env).
- Visibilidad de menú R-AUT-08 **sin** gate `CompanyModules` (aún no existe `companies`)
  — ver excepción en design.
- UI mínima: `AppLayout` (sidebar data-driven + topbar, hueco de logo tenant),
  `ServerTable` real en Index Module/Functionality, kit de forms Create/Edit; menú como
  prop Inertia.
- Login/logout de sesión Laravel (sin registro ni reset password).

## Capabilities

### New Capabilities

- `admin-module-catalog`: entidades globales Module y Functionality (campos, enums,
  slug inmutable, provisión de permisos).
- `rbac-spatie-teams`: Spatie Permission con Teams, Super Admin NULL, Gate::before,
  usuario seed.
- `menu-visibility`: construcción del árbol de menú según R-AUT-08 (parcial: sin
  CompanyModules).
- `admin-ui-login`: AppLayout, ServerTable, forms mínimos, login/logout.

### Modified Capabilities

- `domain-entity-generator`: añade el flag `--global` y el comportamiento descrito en
  `tooling.md` §1 (catálogos sin tenancy).

## Impact

- Dominio `App\Domains\Admin` materializado (Module, Functionality + piezas CRUD).
- Dependencias: `spatie/laravel-permission`, `spatie/laravel-translatable`,
  `tabulator-tables` (+ tipos) si aún no están en `package.json`.
- Frontend: `Layouts/AppLayout.tsx`, `Components/Table/ServerTable.tsx`, páginas Admin,
  shared props de menú vía `HandleInertiaRequests`.
- Auth: rutas login/logout; middleware `auth` en rutas Admin.
- Arch tests: Module/Functionality en `ignoring` de BelongsToCompany.
- Ficha: `docs/modules/Admin.md` (creada con esta propuesta).
- Sin `companies` / CompanyModules / user_companies.
