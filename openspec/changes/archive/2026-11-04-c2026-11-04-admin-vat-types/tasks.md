# Tasks

## 1. Datos — VatType

- [x] 1.1 Migración `vat_types` (country_id FK, name, rate decimal 5,2,
      equivalence_surcharge decimal 5,2 nullable, status string, softDeletes;
      unique country_id+name entre no trashed); sin company_id
- [x] 1.2 Model `VatType` + enum status + belongsTo Country + factory
- [x] 1.3 Seeder desde JSON v1 → country_id=66 (España); IDs preservados; fuera
      de DatabaseSeeder; README (orden: GeographySeeder antes); smoke
- [x] 1.4 Test cross-company: mismo catálogo desde A y B

## 2. Backend

- [x] 2.1 Policy + FormRequests + Actions (CRUD + toggle); country_id required;
      unique name por país
- [x] 2.2 Controller Index/table/store/update/destroy/status + Resource; rutas
      `admin/vat-types*`; table Active-first + name; eager load country;
      props/options países (todos no trashed) para modales
- [x] 2.3 Feature: authz 403, country required, filtro país, orden, CRUD, toggle;
      Index GET no crea filas

## 3. Frontend + i18n

- [x] 3.1 Index ServerTable: país (filtro), name, rate, RE; Acciones; ActionBar
      Nuevo; sin columna Estado
- [x] 3.2 Modales Create/Edit (select país completo + campos); i18n es/en/ca

## 4. Menú / ficha / mapa

- [x] 4.1 Seed Functionality `vat-types` + Spatie `vat-types.*`
- [x] 4.2 MenuBuilder CONFIGURATION_SLUGS + route; feature menú SA incluye Tipos
      de IVA
- [x] 4.3 Actualizar Admin.md, glossary, module-map (VatType en Admin; Tax
      consume)
- [x] 4.4 V-07/V-08 sí; V-09 N/A; E2E N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan; suite feature vat-types verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
