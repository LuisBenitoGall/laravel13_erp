# Tasks

## 1. Frontend CropDialog

- [x] 1.1 Estado `aspect` / ratio seleccionado; default `4/3`; botones
      1:1, 4:3, 16:9, libre (libre = width/height del asset o fallback 4/3);
      al cambiar ratio reset crop `{0,0}` y zoom `1`.
- [x] 1.2 Pasar `aspect={...}` a `Cropper`; texto ayuda i18n visible.
- [x] 1.3 Claves es/en/ca: ayuda + labels ratios.

## 2. Docs y verificación

- [x] 2.1 Línea en ficha Admin Galería: modal crop con ayuda + ratios.
- [x] 2.2 Test fuente o Feature ligero: CropDialog contiene ayuda y los
      cuatro ratios; GalleryTest existentes siguen verdes; Pint dirty.

## 3. Fuera de alcance

- Cambiar lib; API copies; Wysiwyg/picker; cropSize numérico; E2E browser.
