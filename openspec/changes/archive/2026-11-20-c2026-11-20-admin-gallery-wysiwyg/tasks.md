# Tasks

## 1. Dependencia TipTap Image

- [x] 1.1 Añadir `@tiptap/extension-image` (major TipTap 3) en `package.json` /
  lockfile y fila en `docs/standards/libraries.md` junto a TipTap; verificar
  `npm ls @tiptap/extension-image` OK

## 2. Sanitizer HTML (`AllowedHtmlSanitizer`)

- [x] 2.1 Ampliar allowlist con `<img>` y atributos seguros `src` + `alt`
  según design D6 (http(s) o path `/…`; rechazar `javascript:`/`data:`/etc.);
  verificar unit tests keep/reject verdes
- [x] 2.2 Cubrir casos vacíos/self-closing y que tags previos (p, a, lists)
  siguen intactos; verificar suite unit del sanitizer

## 3. Backend picker Galería

- [x] 3.1 Añadir `GET admin/gallery/picker` (`admin.gallery.picker`) + acción
  controller JSON con perímetro idéntico a Index y `authorize viewAny`;
  verificar Feature 200 con `id/name/alt/url` y 403 sin `gallery.view`
- [x] 3.2 Test de perímetro picker: actor solo company A no ve assets de
  company B (R-TEN-05 en listado JSON); verificar Feature de aislamiento

## 4. Frontend Wysiwyg + Article

- [x] 4.1 Integrar extensión Image en `Form/Wysiwyg`, prop opt-in
  `enableGalleryInsert`, botón toolbar + modal embebido que consume
  `admin.gallery.picker` e inserta `setImage({ src, alt })`; verificar en
  UI Article (devtools/manual o assert fuente) y que sin prop no hay botón
- [x] 4.2 Activar `enableGalleryInsert` solo en `ContentForm` cuando
  `kind === Article`; verificar Help no muestra selector
- [x] 4.3 i18n (es + en mínimo) para labels del botón/modal/vacío/error de
  carga; verificar cero literales nuevos en el diff

## 5. Persistencia Contents Article

- [x] 5.1 Feature: guardar Article con body que incluye `<img>` de URL de
  MediaAsset (alt del activo) y, tras GET Edit, el HTML sigue conteniendo
  ese img; verificar test verde
- [x] 5.2 Feature/unit: body con `src` inseguro no persiste esquema peligroso;
  verificar assert

## 6. Vinculaciones / registro / E2E

- [x] 6.1 Checklist V-01..V-20: todos **no** para este change (sin stock,
  ledger, menú nuevo, permisos nuevos, módulo nuevo, etc.); anotar en ficha
  Admin / contents si hace falta mención Fase 2 Wysiwyg — verificar que no
  hay seeder/menú/policy nuevos injustificados
- [x] 6.2 Actualizar ficha de módulo Admin (galería) y/o contents con la
  capacidad «insertar en Wysiwyg Article»; verificar doc coherente con specs
- [x] 6.3 E2E Pest browser: **no aplica** (design D7); verificar que no se
  añaden tasks E2E inventadas
- [x] 6.4 Pint dirty + PHPStan + tests afectados del change en verde;
  verificar comandos OK

## Workflow follow-up

- Revisión humana (Luis) → `/opsx:apply` (20) → 25 → 30 `/opsx:archive`.
- Fase 3 (`MediaPicker` genérico) queda fuera; no archivar como hecha.
