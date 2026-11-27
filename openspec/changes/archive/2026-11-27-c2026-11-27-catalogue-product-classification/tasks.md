# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Actualizar `docs/modules/Catalog.md`: clasificación en Edit
      General; backlog Family↔Category
- [x] 1.2 Glosario / module-map si hace falta (pivotes / family_id)

## 2. Datos — vínculos

- [x] 2.1 Migración: `products.family_id` nullable FK; pivotes
      `category_product` y `product_tag` (unique pares); relaciones en
      Product/Family/Category/Tag; verificar migrate
- [x] 2.2 Factories/states de vínculo si ayudan a tests; verificar

## 3. Backend — update + quick-create tag

- [x] 3.1 Extender `UpdateProduct` + UpdateRequest: `family_id`,
      `category_ids`, `tag_ids`; validación misma company; sync pivotes;
      verificar
- [x] 3.2 Action + ruta quick-create tag (Active; `tags.create`); 403 sin
      permiso; verificar `route:list`
- [x] 3.3 Feature Pest: assign/clear family/categories/tags; ajeno
      rechazado; in situ con/sin `tags.create`; Active; R-TEN-05;
      verificar tests

## 4. Frontend — General + MultiSelect

- [x] 4.1 `Form/MultiSelect` (multi + search; creatable opcional) sin deps
      nuevas; export kit; verificar
- [x] 4.2 Edit/ProductForm General: Select family; MultiSelect categories;
      MultiSelect tags + create si `can.create_tags`; props opciones Active
      (+ vinculadas); i18n; form-layout-polish; Create sin clasificación;
      verificar UX

## 5. Calidad

- [x] 5.1 Pint + PHPStan; suite features del change en verde
      (`php artisan test --compact` filtro classific / product + tag)

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
