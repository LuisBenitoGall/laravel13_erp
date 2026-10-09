# Módulo Admin — catálogo, RBAC y shell

> Ficha playbook §1. **Como quedó** tras `c2026-07-17-admin-catalog-permissions` y
> `c2026-10-09-company-tenancy-modules`.

## Propósito y alcance

**Entra:** catálogo global `Module` / `Functionality`; Spatie Teams; Super Admin;
elegibilidad (`company_module_authorizations`) + activación (`company_modules`);
`MenuBuilder` y `EnsureModuleIsActive` con triple condición R-AUT-08; UI de módulos de
empresa y autorización Super Admin.

**No entra:** roles Spatie por empresa; Platform/pago real de módulos; engorde Company.

## Entidades

| Entidad | Dominio | Notas |
|---|---|---|
| `Module` / `Functionality` | Admin | Catálogo global (`--global`) |
| `CompanyModuleAuthorization` | Admin | Capa 3 elegibilidad (soft delete); pivote, sin CRUD generador |
| `CompanyModule` | Admin | Capa 2 activación (soft delete); pivote, sin CRUD generador |

## Dependencias

- **Admin → Partners** (lectura `Company`): menú, middleware, autorizaciones, pertenencia.
- `ModuleActivationChecker` implementado por `CompanyModuleActivationChecker` (Admin).

## Capas de módulos (como quedó)

1. Global Active/Inactive.
2. Encendido en `company_modules` (permiso `company-modules.*` + Super Admin).
3. Autorizado en `company_module_authorizations` (solo Super Admin; Basic auto al crear
   company activa / al activar Module Basic).

## Functionalities seed

`modules`, `functionalities`, `companies`, `company-modules`,
`company-module-authorizations` bajo Module `admin`.

## Menú / middleware

`MenuBuilder` y `EnsureModuleIsActive` exigen Active + autorizado + encendido para la
empresa de sesión; luego comodín OR `viewAny`. Excepción R-AUT-08 parcial **retirada**.
