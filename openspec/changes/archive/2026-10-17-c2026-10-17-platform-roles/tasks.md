## 1. Datos / esquema

- [x] 1.1 Migración: `roles.universal` (boolean default false), `roles.description`
      (nullable string/text)
- [x] 1.2 Seed/migración de datos: plantillas `manager` y `guest` con `company_id` NULL,
      `universal = true` (idempotente; no tocar rol `Super Admin`)
- [x] 1.3 Functionality `roles` en módulo `admin` (AdminOnly) vía seeder
      (`CreateFunctionality` + permisos auto); menú data-driven (V-07, V-08)

## 2. Backend Admin

- [x] 2.1 Modelo/scopes Spatie Role (o wrapper dominio): `platform()`, plantillas
      universales; fillable/casts de `universal` y `description`
- [x] 2.2 Actions: CreatePlatformRole, UpdatePlatformRole, DeletePlatformRole,
      SyncPlatformRolePermissions (reglas de protegidos + rechazo si hay users)
- [x] 2.3 Extender `EnsureCompanyDefaultRoles`: materializar todos los universales
      (`company_id` NULL, `universal`); manager → sync posterior; resto → copiar
      permisos de plantilla
- [x] 2.4 Comando artisan idempotente `roles:materialize-universals` (backfill companies
      existentes)
- [x] 2.5 Policy `PlatformRolePolicy` (solo Super Admin; resto denegado) + FormRequests
      (name unique en team NULL, universal bool, permissions array)
- [x] 2.6 Controller Inertia + rutas (`index`/`table`/`create`/`store`/`edit`/`update`/
      `destroy`) bajo prefijo Admin; contrato ServerTable

## 3. Frontend

- [x] 3.1 Páginas Inertia: listado ServerTable; create/edit con flag universal +
      description
- [x] 3.2 Matriz de permisos (módulos → funcionalidades → 5 acciones); ocultar AdminOnly
      si universal; manager plantilla solo lectura; genéricos con AdminOnly
- [x] 3.3 i18n es/en/ca (claves snake_case; cero literales)

## 4. Vinculaciones y registro

- [x] 4.1 V-07/V-08: permisos `roles.*` + ítem menú Admin «Roles»
- [x] 4.2 Actualizar `docs/modules/Admin.md` (CRUD platform roles en alcance; custom
      manager sigue fuera)
- [x] 4.3 Glosario: Rol universal / Rol de plataforma (y UI «Invitado» ya existente)
- [x] 4.4 Resto V-01..V-20: no aplica (declarado; sin tasks de stock/ledger/etc.)

## 5. Tests

- [x] 5.1 Feature: CRUD Super Admin; no-SA 403; Super Admin inmutable; manager/guest no
      borrables; delete con users rechazado
- [x] 5.2 Feature: matriz universales sin AdminOnly; genérico puede guardar AdminOnly;
      manager plantilla no altera sync local
- [x] 5.3 Feature: crear company materializa universales existentes; universal nuevo sin
      backfill auto; comando backfill idempotente
- [x] 5.4 Regresión: sync manager (Authorize/Revoke) + guest vacío por defecto
- [x] 5.5 E2E Pest browser: N/A (`e2e.md` §4 — CRUD Admin)

## 6. Calidad / DoD

- [x] 6.1 `composer test`, `vendor/bin/pint`, `vendor/bin/phpstan analyse` verdes
- [x] 6.2 `openspec validate c2026-10-17-platform-roles` limpio
