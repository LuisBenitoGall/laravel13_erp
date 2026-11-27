# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Revisar glosario: confirmar `Product` / Module `catalogue` (añadir
      fila Module si falta) y verificar que el naming coincide con design D2
- [x] 1.2 Ajustar si hace falta `docs/modules/Catalog.md` (ya esbozada en
      propose) a playbook §1 completo y alineada a design/specs
- [x] 1.3 Actualizar `docs/standards/module-map.md`: Catalog con Product
      operativo; precios/familias/tarifas siguen pendientes; dependencia
      Catalog → Admin = D
- [x] 1.4 Actualizar `docs/standards/capabilities-map.md`: ejemplo Catalog
      oleada 1 (punters a `catalogue-module` / `catalogue-products`)

## 2. Datos — Product

- [x] 2.1 Migración `products` (`company_id`, `name`, `type`, `status`,
      softDeletes, auditable columns) + índices; Model `Product`
      BelongsToCompany + SoftDeletes + Auditable; enums `ProductType`,
      `ProductStatus` (`canTransitionTo` Active↔Inactive); Factory con
      states; verificar migrate fresh en local
- [x] 2.2 Unit Pest enums (transiciones) + factory crea en company; verificar
      `php artisan test --compact` filtrado a esos tests

## 3. Module / menú / authz (V-07, V-08, V-09, V-21)

- [x] 3.1 Alta Module `catalogue` (Basic) + Functionality `products` en
      `RealignMenuCatalog` / seeders; `MenuFunctionalityHref` →
      `/admin/catalogue`; incluir en AuthorizeBasicModules / sync manager;
      verificar seed + menú Catálogo
- [x] 3.2 `ProductPolicy` (tenancy + Spatie) registrada; permisos
      `products.*` + `catalogue.access`; matriz manager/guest según ficha;
      verificar Policy discovery / gate
- [x] 3.3 Feature V-21: módulo off bloquea; sin permiso 403; capas+permiso
      OK en `/admin/catalogue` — verificar tests verdes

## 4. Backend — Product CRUD

- [x] 4.1 Actions `CreateProduct` (fuerza status Inactive; no lee type de
      update path), `UpdateProduct` (no muta `type`), `DeleteProduct`
      (soft); FormRequests Store/Update (Update sin type editable);
      Resources; Controller delgado + `table`; rutas
      `routes/domains/catalog.php` bajo `module:catalogue` prefijo
      `admin/catalogue` names `admin.catalogue.*`; verificar route:list
- [x] 4.2 Feature Pest CRUD feliz + validación; Create → Inactive; Update
      no cambia type; soft delete; R-TEN-05 aislamiento; activity/audit
      (V-20) — verificar tests verdes

## 5. Frontend — Index / Create / Edit

- [x] 5.1 Tipos TS `resources/js/types/catalog.d.ts`; Index
      `Catalog/Products/Index` con `<ServerTable>` (name, type, status);
      i18n es/en(+ca si patrón); permisos UI; verificar build/types
- [x] 5.2 Create: form solo name + type; redirect a Edit; verificar UX
- [x] 5.3 Edit: `RecordEditLayout` + tab General (name, status);
      `RecordSummary` con type RO + auditoría; sin tabs stub; i18n;
      verificar form-layout-polish

## 6. Calidad

- [x] 6.1 Pint (`vendor/bin/pint --dirty --format agent`) + PHPStan en PHP
      tocado; suite features del change en verde
      (`php artisan test --compact` filtros catalogue/product)

## Workflow follow-up

- Revisión Luis de la propuesta → `/opsx-apply` (20) → 25 → 30 archive
  (sin 40; E2E N/A).
