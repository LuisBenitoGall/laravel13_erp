# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Glosario: `Tag` (entidad; Functionality `tags` / menú Etiquetas)
- [x] 1.2 `docs/modules/Catalog.md`: oleada Tag; backlog clasificación
      ficha → Family↔Category
- [x] 1.3 `module-map.md`: Tag maestro operativo

## 2. Datos — Tag

- [x] 2.1 Migración `tags` (`company_id`, `name`, `slug`, `status`,
      softDeletes, auditable); unique slug/company; Model + enum
      `TagStatus`; Factory; verificar migrate
- [x] 2.2 Unit/factory slug/states; verificar tests

## 3. Module / menú / authz (V-07, V-08, V-21)

- [x] 3.1 Realign: Functionality `tags`; map sort; href `/products/tags`;
      sync manager `tags.*`; verificar menú Etiquetas
- [x] 3.2 `TagPolicy`; Feature V-21 tags; verificar tests

## 4. Backend — Tag CRUD

- [x] 4.1 Actions Create/Update/Delete (slug auto; update no retoca slug);
      FormRequests; Controller + table; rutas `products/tags*` antes de
      `{product}`; verificar `route:list`
- [x] 4.2 Feature Pest: CRUD; slug; R-TEN-05; soft delete; audit V-20;
      verificar tests

## 5. Frontend — Index / Create / Edit

- [x] 5.1 Tipos TS; Index ServerTable (name, slug, status); i18n
- [x] 5.2 Create/Edit: name, status; slug RO en Edit; form-layout-polish;
      verificar menú Catálogo→Etiquetas

## 6. Calidad

- [x] 6.1 Pint + PHPStan; suite features del change en verde
      (`php artisan test --compact` filtro tags / Tag)

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
