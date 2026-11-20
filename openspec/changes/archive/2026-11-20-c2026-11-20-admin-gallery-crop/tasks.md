# Tasks

## 1. Canon y deps

- [x] 1.1 Añadir `react-easy-crop` a `package.json` y a `libraries.md`
      (Galería crop).
- [x] 1.2 Actualizar fila Galería en `docs/modules/Admin.md` (1b crop/copia
      hecha; sin parent_id).

## 2. Backend

- [x] 2.1 Action `CreateMediaAssetCopy`: desde origen en perímetro + fichero
      blob; `company_id` null; hereda name/alt; no muta origen; mismos
      límites mime/KB que gallery config.
- [x] 2.2 FormRequest + authorize (`create` + view origen); `company_id`
      prohibited.
- [x] 2.3 Ruta `POST admin/gallery/{media_asset}/copies` + método controller;
      flash éxito i18n.

## 3. Frontend

- [x] 3.1 Modal crop (react-easy-crop + zoom, aspect libre) desde card;
      export canvas → Blob → FormData a copies.
- [x] 3.2 Icono/acción crop en card gated `can.create`; i18n es/en/ca;
      toast/redirect Index.
- [x] 3.3 Sin ServerTable; no introducir Volver.

## 4. Tests

- [x] 4.1 Feature: POST copies crea activo nuevo con name/alt heredados;
      original path/name/alt intactos; fichero nuevo en disk.
- [x] 4.2 Feature: sin create / origen fuera de perímetro → 403; company_id
      en body → error validación.
- [x] 4.3 Assert Index incluye acción crop (source) + Pint dirty + tests
      Gallery/crop verdes.

## 5. Fuera de alcance

- Wysiwyg/MediaPicker; parent_id; overwrite original; upload empresa;
  Intervention; E2E browser; ratios fijos obligatorios.
