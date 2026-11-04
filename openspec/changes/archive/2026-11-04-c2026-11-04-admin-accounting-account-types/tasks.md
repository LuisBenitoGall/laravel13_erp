# Tasks

## 1. Datos + i18n fiscal

- [x] 1.1 Migración `accounting_account_types` (autoreference, code, name, mode
      nullable, softDeletes; sin company_id; sin status)
- [x] 1.2 Model + factory; level computed strlen(code)
- [x] 1.3 Portar claves `fiscal.*` (es + en/ca según D3) desde v1
- [x] 1.4 Seeder portado PGC (estructura/lógica v1 + i18n); fuera de
      DatabaseSeeder; README; smoke code `1` / count
- [x] 1.5 Test cross-company: mismo catálogo desde A y B

## 2. Backend

- [x] 2.1 Policy + FormRequests + Actions (CRUD); Update no muta code; delete
      bloqueado si hijos no trashed
- [x] 2.2 Controller Index/table/store/update/destroy + show + Resource; rutas
      `admin/accounting-account-types*`; table orderBy code
- [x] 2.3 Feature: authz, CRUD, code inmutable, delete con hijos, show 200,
      seed smoke

## 3. Frontend + i18n UI

- [x] 3.1 Index ServerTable (code, name, level) + Acciones Edit/Delete; ActionBar
      Nuevo + acceso a show; modales Create/Edit
- [x] 3.2 Page Show árbol jerárquico desde props/BD; i18n UI es/en/ca

## 4. Menú / ficha / mapa

- [x] 4.1 Seed Functionality `accounting-account-types` + Spatie permisos
- [x] 4.2 MenuBuilder CONFIGURATION_SLUGS + route; feature menú SA
- [x] 4.3 Admin.md, glossary (Grupo contable / AccountingAccountType),
      module-map
- [x] 4.4 V-07/V-08 sí; V-09 N/A; E2E N/A; select cascada N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan; suite feature verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
