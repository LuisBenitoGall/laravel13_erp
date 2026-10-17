---
name: tenancy-rbac
description: >-
  Invariantes de tenancy, User/Company, Module/Functionality y las tres capas
  SaaS del ERP v2. Usar al crear o tocar modelos, policies, menú, middleware de
  módulo, permisos Spatie, login/sesión de empresa, Super Admin, o cualquier
  entidad con company_id.
---

# Tenancy + RBAC (ERP v2)

Leer entero `docs/standards/kernel.md` y aplicar. Detalle `R-TEN-*` / `R-AUT-*`
en `docs/standards/architecture.md`. Ficha viva: `docs/modules/Admin.md`.

## Checklist (cada entidad / cada PR)

1. ¿Es catálogo global (`--global`) o dato de tenant?
   - Global: Module, Functionality, países, IVA base… **sin** `BelongsToCompany`.
   - Tenant: trait `BelongsToCompany` + `company_id` + índices `(company_id, …)`.
   - Company **es** el tenant: ni global ni scoped.
2. FormRequests: `exists`/`unique` acotados al tenant (R-TEN-07).
3. Policy: (a) pertenencia a la company de sesión (b) permiso Spatie
   `<functionality>.<acción>`. Nada de middleware `*Get/*Post`.
4. Test de aislamiento cruzado (R-TEN-05) — sin él la entidad no está terminada.
5. Si es navegable: Functionality + 5 permisos auto-provisionados, no a mano.
6. Rutas del módulo: `EnsureModuleIsActive:<slug>` (triple capa, no solo permiso).

## Tres capas — no saltarse ninguna

Un Module es usable en sesión solo si:

1. `modules.status = Active`
2. fila en `company_module_authorizations` para esa company
3. fila en `company_modules` (encendido)

Basic se autoriza solo. Optional y AdminOnly no. Encender sin autorizar debe
fallar. Apagar no borra permisos del rol.

Menú: tras las 3 capas, comodín `<module>.access` **OR** `viewAny` de alguna
Functionality. Nunca AND estricto de v1.

## Super Admin

`Gate::before` → true en habilidades. Datos: solo companies vinculadas al user.
No crear atajos “si es Super Admin, ignore company_id”.

## User sin company

Puede persistir en `users`. Login al ERP → rechazo (mensaje i18n), sin sesión
de aplicación. Helpers de test: `actingWithCompany()` / `withCurrentCompany()`.

## Prohibido

- `session('currentCompany')`
- Permisos con slug de Module (salvo `.access`) o nombres v1 (`index`, `search`,
  `edit`+`update` separados, `destroy`)
- Paquetes de tenancy
- Inventar semántica AdminOnly extra o “ver todas las empresas”
