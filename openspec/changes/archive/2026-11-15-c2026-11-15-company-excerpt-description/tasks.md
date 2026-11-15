# Tasks

## 1. Datos Company (excerpt / description)

- [x] 1.1 Migración: columnas JSON nullable `excerpt` y `description`
      `after('parent_id')`. Verificar `migrate`.
- [x] 1.2 Modelo `Company`: `HasTranslations` + fillable; factory states
      opcionales. Verificar unit/factory.
- [x] 1.3 Glosario: `excerpt` / `description` de Company (texto breve /
      rico multi-idioma). Verificar fila en `glossary.md`.

## 2. Backend Update

- [x] 2.1 `UpdateCompanyRequest` (o equivalente): `excerpt.*` max:1000;
      `description.*` max:20000; locales activos. Verificar Feature
      validación.
- [x] 2.2 Persistencia Update + sanitización HTML allowlist (D3). Verificar
      Feature guarda traducciones y elimina tags no permitidos.
- [x] 2.3 Create sin aceptar/exigir excerpt/description. Verificar Feature
      Create.
- [x] 2.4 Perímetro: actor sin vínculo no actualiza textos de company ajena
      (R-TEN-05 / Policy update). Verificar 403 Feature.

## 3. Kit Form Wysiwyg

- [x] 3.1 Instalar `@tiptap/react` + `@tiptap/starter-kit` (peers). Verificar
      `package.json` / build.
- [x] 3.2 Componente `Form/Wysiwyg` + export barrel; documentar en
      `frontend.md` (Wysiwyg implementado) y mover entrada en
      `libraries.md`. Verificar source + docs.
- [x] 3.3 Smoke: Wysiwyg usable con Field (story o uso en Company Edit).
      Verificar TypeScript/build.

## 4. Frontend Company Edit / Create

- [x] 4.1 Edit tab Información: `LangTabs` + excerpt Textarea (maxLength
      1000) + description Wysiwyg; props desde traducciones. Verificar UI.
- [x] 4.2 Create sin inputs excerpt/description. Verificar UI/source.
- [x] 4.3 i18n es/en/ca labels. Verificar keys.
- [x] 4.4 Actualizar `docs/modules/Admin.md` (campos Info). Verificar ficha.
- [x] 4.5 Feature/regresión Edit+Create + Pint + PHPStan verdes.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Consumo en Directorio: change siguiente.

## Correcciones 25 → 20

- [x] HIGH: `AllowedHtmlSanitizer` reescribe openings allowlist sin attrs;
      solo `<a href>` http(s)/mailto. Feature cubre onclick/onmouseover.
- [x] LOW: elimina `script`/`style`/iframe/object/embed/noscript con
      contenido; Feature no deja `alert(1)` huérfano.
