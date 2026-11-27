# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Glosario: `Category` (árbol N, Functionality `categories`)
- [x] 1.2 `docs/modules/Catalog.md`: oleada Category; backlog Tag →
      clasificación → Family↔Category
- [x] 1.3 `module-map.md`: Category maestro operativo

## 2. Datos — Category

- [x] 2.1 Migración `categories` (`company_id`, `name`, `slug`, `status`,
      `parent_id`, softDeletes, auditable); unique slug/company; Model +
      enum `CategoryStatus` + relaciones parent/children; Factory;
      verificar migrate
- [x] 2.2 Helpers anti-ciclo / descendientes; unit tests; verificar

## 3. Module / menú / authz (V-07, V-08, V-21)

- [x] 3.1 Realign: Functionality `categories`; map sort; href
      `/products/categories`; sync manager `categories.*`; verificar menú
- [x] 3.2 `CategoryPolicy`; Feature V-21 categories; verificar tests

## 4. Backend — Category CRUD + árbol

- [x] 4.1 Actions Create/Update/Delete (slug auto; bloqueo delete con
      hijos; validación parent misma company + anti-ciclo); FormRequests;
      Controller + table; rutas `products/categories*` antes de
      `{product}`; verificar `route:list`
- [x] 4.2 Feature Pest: CRUD; hija/raíz; ciclo rechazado; delete con
      hijos bloqueado; R-TEN-05; soft delete hoja; verificar tests

## 5. Frontend — Index / Create / Edit

- [x] 5.1 Tipos TS; Index ServerTable (name, parent, slug, status); i18n
- [x] 5.2 Create/Edit: name, status, Select padre (excluir self+desc en
      Edit); slug RO en Edit; form-layout-polish; verificar menú
      Categorías

## 6. Calidad

- [x] 6.1 Pint + PHPStan; suite features del change en verde
      (`php artisan test --compact` filtro categor)

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
