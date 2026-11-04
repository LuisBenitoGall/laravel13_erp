# Tasks

## 1. Datos — Unit

- [x] 1.1 Migración global `units` (name, slug, symbol nullable; status string;
      softDeletes; sin `company_id`; sin translations); verificar migrate
- [x] 1.2 Model `Unit` + enum status + SoftDeletes + factory
- [x] 1.3 Seeder desde JSON/dump v1 con IDs y status preservados; fuera de
      DatabaseSeeder; documentar en README § Seeders; smoke count + id=1
- [x] 1.4 Test cross-company: mismo catálogo desde company A y B

## 2. Backend

- [x] 2.1 Policy + FormRequests + Actions (CRUD + toggle); name unique; symbol
      max 4
- [x] 2.2 Controller Index/table/store/update/destroy/status + Resource; rutas
      `admin/units*`; table Active-first + name si sorts vacío
- [x] 2.3 Feature: authz 403, listado/orden, create/update, toggle, delete; Index
      GET no crea filas

## 3. Frontend + i18n

- [x] 3.1 Page Index Units: ServerTable (name, symbol) + Acciones; ActionBar
      Nuevo; breadcrumb Configuración; sin columna Estado
- [x] 3.2 Modales Create/Edit + i18n es/en/ca

## 4. Menú / ficha / mapa

- [x] 4.1 Seed Functionality `units` + Spatie `units.*`
- [x] 4.2 MenuBuilder CONFIGURATION_SLUGS + route; feature menú SA incluye
      Unidades
- [x] 4.3 Actualizar `docs/modules/Admin.md`, `glossary.md`, `module-map.md`
      (Unit en Admin; Catalog consume a futuro)
- [x] 4.4 V-07/V-08 sí; V-09 N/A; E2E N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan; suite feature units verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
