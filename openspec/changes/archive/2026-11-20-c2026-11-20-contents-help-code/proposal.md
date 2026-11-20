# Proposal

## Why

Al crear Contenidos de tipo Información, el `code` se genera con `Str::slug(nombre)`,
que sustituye espacios por guiones y elimina puntos. Eso impide anclas HelpTip del
tipo `companies.name` / `companies.nif` desde la UI Create, aunque la convención
`{helpScope}.{fieldKey}` ya esté acordada. Hay que poder introducir el código a
mano en el alta de Información.

## What Changes

- Create de Content: si el tipo es Información (`help`), el actor **introduce** el
  `code` (obligatorio); se persiste tal cual (normalizado a minúsculas), **sin**
  `UniqueSlug` / `Str::slug`.
- Validación de formato que admite segmentos con `.` (y `-` para códigos legacy
  tipo `contents-admin`); unicidad contra `contents` (incl. soft-deleted).
- Si el tipo es Artículo (`article`), se mantiene el comportamiento actual: `code`
  automático desde el nombre vía `UniqueSlug`.
- Edit: el `code` sigue siendo inmutable (sin cambio de alcance).
- Documentar en glosario/ficha Admin el ritual Create Help con code explícito.
- Tests Pest del Store Create Help con code `companies.name` y rechazo de
  formato/duplicado.

**No BREAKING** para Artículo ni para Contents ya creados; los mal slugificados
(`companies-name`) no se migran en este change (limpieza manual o change aparte).

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `contents-create`: Create mínimo pasa a exigir `code` cuando kind=Help; Article
  conserva generación automática.

## Impact

- Backend: `StoreContentRequest`, `CreateContent`, tests Feature Create Content.
- Frontend: `resources/js/Pages/Admin/Contents/Create.tsx` (+ i18n).
- Docs: `docs/standards/glossary.md` (ancla), fila Contenidos en ficha Admin si
  aplica.
- Sin migraciones de esquema; sin cambios en HelpTip / HelpContentMap / Edit.
- E2E Pest browser: no aplica (flujo Create Content no listado como flujo §4 e2e
  crítico en este change; coverage Feature HTTP).
