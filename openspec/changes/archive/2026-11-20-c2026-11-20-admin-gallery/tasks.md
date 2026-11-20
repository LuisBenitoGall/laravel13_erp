# Tasks

## 1. Datos y canon

- [x] 1.1 Migración `media_assets`: id, company_id nullable FK, name, alt,
      path, mime nullable, width/height/bytes nullable, timestamps,
      softDeletes; índices `(company_id)`, path unique.
- [x] 1.2 Model `App\Domains\Admin\Models\MediaAsset` + Factory (estados
      `forCompany(Company)` / plataforma null). Sin BelongsToCompany.
- [x] 1.3 `config/gallery.php` (mimes + max_kilobytes 2048).
- [x] 1.4 Glosario `MediaAsset` / Galería; `libraries.md`: marcar
      `react-dropzone` en uso Galería; fila ficha `docs/modules/Admin.md`
      + vinculaciones V-07/V-08/V-21.

## 2. Backend

- [x] 2.1 Actions: `CreateMediaAsset` (force company_id null; reject non-null),
      `UpdateMediaAsset` (name/alt), `DeleteMediaAsset` (soft delete + borrar
      fichero disco según design).
- [x] 2.2 FormRequests Store/Update + Policy `MediaAssetPolicy` (viewAny/view/
      create/update/delete; perímetro company_id).
- [x] 2.3 `MediaAssetController` Index/Store/Update/Destroy; Resource con
      `url`, name, alt, mime, dimensions.
- [x] 2.4 Rutas `admin.gallery.*` + `EnsureModuleIsActive:configuracion` (o
      patrón idéntico a contents).

## 3. Menú y permisos (V-07 / V-08)

- [x] 3.1 `RealignMenuCatalog`: `ensureGallery`; `gallery` tras `contents` en
      `FUNCTIONALITY_MAP`.
- [x] 3.2 `MenuFunctionalityHref` + labels menú i18n (`galeria`).
- [x] 3.3 Confirmar auto-provision `gallery.*` vía CreateFunctionality.

## 4. Frontend

- [x] 4.1 Instalar/usar `react-dropzone` si no está en package.json.
- [x] 4.2 Página `Admin/Gallery/Index.tsx`: dropzone + grid `lg:grid-cols-6`,
      card thumb, copiar URL, modal/panel edit name/alt, delete confirm; i18n
      es/en/ca.
- [x] 4.3 Sin ServerTable en Index (excepción documentada).

## 5. Tests

- [x] 5.1 Feature: store null OK; store con company_id → rechazo; update
      name/alt; delete soft; validación mime/peso.
- [x] 5.2 Feature authz: sin permiso → 403.
- [x] 5.3 R-TEN-05: asset company B no mutable/listable por actor solo en A.
- [x] 5.4 V-21: acceso módulo configuracion / permiso gallery (plantilla
      templates-backend §13 adaptada).
- [x] 5.5 Pint dirty + Feature Gallery en verde.

## 6. Fuera de alcance (no implementar)

- Crop/resize copia; MediaPicker; Wysiwyg insert; upload no-SA/otra URL;
  parent_id; thumbs WP; E2E browser; activity log; migración File_ v1.
