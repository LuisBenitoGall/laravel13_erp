# Tasks

## 1. Datos — Currency

- [x] 1.1 Migración global `currencies` (name, slug, code nullable, number
      nullable, symbol nullable max 8, status string; softDeletes; sin
      `company_id`); verificar migrate
- [x] 1.2 Model `Currency` + enum status Active/Inactive + SoftDeletes; factory
- [x] 1.3 Seeder desde dump/JSON v1 con **IDs preservados**; Active solo
      EUR/USD/GBP/CHF; Inactive el resto; símbolos mapa v1 si null; **fuera** de
      `DatabaseSeeder`; documentar en `README.md` § Seeders; smoke: EUR id=2
      Active, count > 0
- [x] 1.4 Test cross-company: mismo catálogo visible desde company A y B
      (sustituto R-TEN-05 para `--global`)

## 2. Backend Currency

- [x] 2.1 Policy + FormRequests + Actions (CRUD + toggle status); Update **no**
      muta code/number; soft delete
- [x] 2.2 Controller Index/table/store/update/destroy/status + Resource; rutas
      `admin/currencies*`
- [x] 2.3 Feature: authz 403, listado, create (unique code/number), update
      inmutable code/number, toggle, delete; Index GET no crea filas

## 3. Frontend + i18n

- [x] 3.1 Page Index Currencies: ServerTable (name, code, number, symbol) +
      Acciones (toggle → Edit modal → Delete); ActionBar Nuevo; breadcrumb
      Configuración; **sin columna Estado**
- [x] 3.2 Modales Create (name, code, number, symbol, status) / Edit (name,
      symbol; code/number readonly o ausentes); kit Form v2
- [x] 3.3 i18n es/en/ca (monedas, acciones, errores unique)

## 4. Menú / Functionality / ficha

- [x] 4.1 Seed Functionality `currencies` (módulo admin) + Spatie `currencies.*`
      vía CreateFunctionality / AdminCatalogSeeder si no existe
- [x] 4.2 `MenuBuilder` CONFIGURATION_SLUGS + route map → Index currencies;
      feature menú SA incluye Monedas
- [x] 4.3 Actualizar `docs/modules/Admin.md` + `glossary.md` (Currency/Moneda)
- [x] 4.4 V-07 sí; V-08 sí; V-09 N/A; E2E N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan en PHP tocado; suite feature monedas verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
