# Tasks

## 1. Registro normativo (docs)

- [x] 1.1 Añadir `ProductImage` / Imagen de producto al glosario
      (`docs/standards/glossary.md`)
- [x] 1.2 Actualizar `docs/modules/Catalog.md`: oleada imágenes (tab Edit,
      `product_images`, disco privado, featured/public, elegibilidad
      Active∧public); NonGoals Documentos/Index thumbs
- [x] 1.3 Confirmar `module-map.md`: sin dependencia de dominio nueva
      (Catalog → Admin sigue D); no tocar capa

## 2. Datos — ProductImage

- [x] 2.1 Migración `product_images` (`product_id`, `company_id`, path,
      `featured`, `public`, softDeletes, unique path+deleted, featured
      generated column + unique como UserImage); Model en
      `App\Domains\Catalog` (+ relaciones Product); Factory; config
      `config/product_images.php` (límites = user_images); verificar migrate
- [x] 2.2 Helper elegibilidad `isEligibleOutsideEdit` (Active ∧ public);
      unit/feature mínimo del helper; verificar tests

## 3. Backend — authz y HTTP (V-21, R-TEN-05)

- [x] 3.1 `ProductImagePolicy` delegando en `ProductPolicy` + company;
      registrar Policy; Actions `UploadProductImages`,
      `DeleteProductImage` (borra fichero), `MarkProductImageFeatured`,
      `UpdateProductImagePublic`; FormRequests; verificar Policy
- [x] 3.2 `ProductImageController` + rutas nested
      `products/{product}/images*` (store/file/destroy/featured/public)
      bajo `module:products`; disco `local`; path
      `companies/{company_id}/products/{product_id}/images/…`; verificar
      `route:list`
- [x] 3.3 Feature Pest: upload OK/límites mime+peso+20; featured único;
      toggle public; delete; entrega auth (guest 401/403; editor ve
      no-public); R-TEN-05 company ajena; elegibilidad Outside Edit;
      verificar tests verdes

## 4. Frontend — tab Imágenes

- [x] 4.1 Tipos TS imagen de producto; `ProductImagesPanel` (Dropzone kit,
      grid, lightbox, featured, public, delete+confirm) calcado a
      `UserImagesPanel`; i18n es/en(+ca si patrón)
- [x] 4.2 Cablear tab `images` en `Catalog/Products/Edit` tras General;
      pasar props/can manage; físico y servicio; sin tab Documentos;
      verificar form-layout-polish / UX

## 5. Calidad

- [x] 5.1 Pint (`vendor/bin/pint --dirty --format agent`) + PHPStan en PHP
      tocado; suite features del change en verde
      (`php artisan test --compact` filtro product image)

## Workflow follow-up

- Revisión Luis de la propuesta → `/opsx-apply` (20) → 25 → 30 archive
  (sin 40; E2E N/A).
