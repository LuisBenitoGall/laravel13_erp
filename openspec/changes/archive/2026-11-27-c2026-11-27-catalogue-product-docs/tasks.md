# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Añadir `ProductDoc` / Documento de producto al glosario
- [x] 1.2 Actualizar `docs/modules/Catalog.md`: oleada documentos (tab Edit,
      `product_docs`, public, disco privado, elegibilidad Active∧public);
      NonGoals Index/consumo
- [x] 1.3 Confirmar `module-map.md`: sin dependencia de dominio nueva

## 2. Datos — ProductDoc

- [x] 2.1 Migración `product_docs` (`product_id`, `company_id`, `path`,
      `original_name`, `mime`, `size_bytes`, `public`, softDeletes,
      unique path+deleted); Model Catalog + relaciones; Factory; config
      `config/product_docs.php` (límites = user_docs); verificar migrate
- [x] 2.2 Helper `isEligibleOutsideEdit` (Active ∧ public); unit test;
      verificar tests

## 3. Backend — authz y HTTP (V-21, R-TEN-05)

- [x] 3.1 `ProductDocPolicy` espejo de `ProductImagePolicy`; Actions
      `UploadProductDocs`, `DeleteProductDoc` (borra fichero),
      `UpdateProductDocPublic`; FormRequests; verificar Policy
- [x] 3.2 `ProductDocController` + rutas nested
      `products/{product}/docs*` (store/download/destroy/public) bajo
      `module:products`; path
      `companies/{company_id}/products/{product_id}/docs/…`; verificar
      `route:list`
- [x] 3.3 Feature Pest: upload OK/límites; toggle public; delete; download
      auth (guest bloqueado; editor descarga no-public); R-TEN-05;
      elegibilidad Outside Edit; verificar tests verdes

## 4. Frontend — tab Documentos

- [x] 4.1 Tipos TS; `ProductDocsPanel` (Dropzone, listado, download, public,
      delete+confirm) calcado a `UserDocsPanel` + toggle public; i18n
- [x] 4.2 Cablear tab `docs` en `Catalog/Products/Edit` tras `images`;
      props/can manage/limits; físico y servicio; verificar UX

## 5. Calidad

- [x] 5.1 Pint (`vendor/bin/pint --dirty --format agent`) + PHPStan en PHP
      tocado; suite features del change en verde
      (`php artisan test --compact` filtro product doc)

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
