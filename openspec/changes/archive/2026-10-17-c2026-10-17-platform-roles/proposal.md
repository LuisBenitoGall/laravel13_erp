## Why

Fase 0 exige shell RBAC usable: hoy existen `manager`/`guest` por empresa y sync, pero
Super Admin no tiene UI para gestionar el catálogo de roles de plataforma (universales
vs solo-plataforma) ni una matriz de permisos alineada al catálogo Module/Functionality.
Sin eso no se pueden definir roles de operador ni futuros defaults de empresa.

## What Changes

- CRUD Inertia de **roles de plataforma** (solo Super Admin): listar, crear, editar;
  borrar solo roles no protegidos.
- Clasificación **universal** (uso tenant / defaults de empresa) vs **genérico de
  plataforma** (no usable por tenants).
- Roles protegidos: `Super Admin` inmutable (fuera del CRUD); plantillas universales
  `manager` y `guest` no borrables.
- Matriz de permisos por módulo/funcionalidad (acciones canónicas); universales **sin**
  AdminOnly; techo tenant sigue siendo módulo autorizado+activado (acceso total a sus
  funcionalidades); el sync del `manager` local **no** se recorta por la plantilla.
- Columnas en `roles`: `universal`, `description` (y plantillas `company_id` NULL para
  defaults).
- Functionality Admin `roles` + menú; i18n; ficha Admin + glosario.
- **No** asignación User↔rol en UI (salvo onboarding ya vigente).
- **No** roles custom del manager de tenant (change posterior).

## Capabilities

### New Capabilities

- `platform-roles`: CRUD Super Admin de roles con `company_id` NULL; flag universal vs
  plataforma; matriz de permisos; protección Super Admin / manager / guest; materialización
  de universales al crear Company.

### Modified Capabilities

- `company-roles`: aclarar que `manager`/`guest` son defaults universales no borrables;
  materialización desde plantillas universales; sync del manager local no limitado por
  matriz de plantilla.
- `manager-permission-sync`: reafirmar techo modular (sin recorte por plantilla universal).

## Impact

- Migración `roles.universal`, `roles.description`; seed plantillas universales
  manager/guest (`company_id` NULL).
- Dominio Admin: Actions CRUD + sync permisos de rol plataforma; Policy; FormRequests;
  Controllers Inertia; páginas React (list + edit con matriz).
- `EnsureCompanyDefaultRoles`: materializar todos los universales (no solo manager/guest).
- Seeder: Functionality `roles` en módulo `admin`; menú.
- Specs/tests Feature; ficha `docs/modules/Admin.md`; `docs/standards/glossary.md`.
- E2E Pest browser: N/A (CRUD, no flujo crítico de `e2e.md` §4).
