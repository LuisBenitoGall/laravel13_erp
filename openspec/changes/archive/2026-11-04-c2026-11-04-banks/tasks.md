# Tasks

## 1. Dominio + datos Bank

- [x] 1.1 Actualizar `docs/standards/module-map.md`: dominio Banks (Capa 1);
      quitar `Bank` de Treasury; glosario `Bank`/`Bancos`
- [x] 1.2 Ficha `docs/modules/Banks.md` (playbook §1 + vinculaciones V-07/V-08)
- [x] 1.3 Migración global `banks` (campos v1 + status string + softDeletes; sin
      `company_id`); verificar migrate
- [x] 1.4 Model `Bank` + enum status + SoftDeletes + factory
- [x] 1.5 Seeder desde enriched JSON con **IDs y status preservados**; fuera de
      DatabaseSeeder; documentar en `README.md` § Seeders; smoke count + algún
      Active id conocido
- [x] 1.6 Test cross-company: mismo catálogo desde company A y B

## 2. Backend

- [x] 2.1 Policy + FormRequests + Actions (CRUD + toggle); SWIFT pad a 11;
      confirmar unique `eu_code` según datos JSON (D5)
- [x] 2.2 Controller Index/table/store/update/destroy/status + Resource; rutas
      `admin/banks*`; table: Active-first + name si sorts vacío
- [x] 2.3 Feature: authz 403, listado/orden, create/update, toggle, delete; Index
      GET no crea filas

## 3. Frontend + i18n

- [x] 3.1 Page Index Banks: ServerTable + Acciones (toggle → Edit → Delete);
      ActionBar Nuevo; breadcrumb Configuración; sin columna Estado
- [x] 3.2 Modales Create/Edit (campos v1 acordados; kit Form); i18n es/en/ca

## 4. Menú / Functionality

- [x] 4.1 Seed Functionality `banks` (módulo admin) + Spatie `banks.*`
- [x] 4.2 MenuBuilder CONFIGURATION_SLUGS + route → Index banks; feature menú SA
      incluye Bancos
- [x] 4.3 V-07/V-08 sí; V-09 N/A; E2E N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan en PHP tocado; suite feature banks verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).

### Nota apply (D5)
- `eu_code` unique entre no trashed: JSON enriched sin colisiones (0 dupes) → unique aplicado.
