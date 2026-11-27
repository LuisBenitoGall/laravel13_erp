# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Actualizar glosario: `Family` + Functionality `families` / menú
      Familias bajo Module `products` (Catálogo)
- [x] 1.2 Actualizar `docs/modules/Catalog.md`: oleada Family (maestro;
      sin vínculo Product); backlog Category/Tag/clasificación /
      Family↔Category N:N
- [x] 1.3 Actualizar `module-map.md`: Family maestro operativo; vínculo
      producto pendiente

## 2. Datos — Family

- [x] 2.1 Migración `families` (`company_id`, `name`, `slug`, `status`,
      softDeletes, auditable); unique slug por company; Model + enum
      `FamilyStatus`; Factory; verificar migrate
- [x] 2.2 Unit/factory: slug generation helper / states; verificar tests

## 3. Module / menú / authz (V-07, V-08, V-21)

- [x] 3.1 Realign: Functionality `families` bajo `products`; map sort;
      labels Module→Catálogo, `catalogue`→Artículos; `MenuFunctionalityHref`
      → `/products/families`; sync manager `families.*`; verificar seed/menú
- [x] 3.2 `FamilyPolicy` + registro; Feature V-21 familias (módulo off /
      sin permiso / OK); verificar tests

## 4. Backend — Family CRUD

- [x] 4.1 Actions Create (slug auto, default status Inactive si vacío) /
      Update (no retocar slug) / Delete soft; FormRequests; Controller +
      `table`; rutas `products/families*` **antes** de `{product}`;
      verificar `route:list`
- [x] 4.2 Feature Pest: CRUD; slug; R-TEN-05; soft delete; audit V-20;
      verificar tests verdes

## 5. Frontend — Index / Create / Edit

- [x] 5.1 Tipos TS; Index ServerTable (name, slug, status); i18n;
      permisos UI; verificar
- [x] 5.2 Create/Edit: name, status; slug RO en Edit (o solo create);
      form-layout-polish; verificar UX menú Catálogo→Artículos/Familias

## 6. Calidad

- [x] 6.1 Pint + PHPStan en PHP tocado; suite features del change en verde
      (`php artisan test --compact` filtro famil)

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
