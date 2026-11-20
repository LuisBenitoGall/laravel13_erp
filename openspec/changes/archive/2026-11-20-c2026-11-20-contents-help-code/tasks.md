# Tasks

## 1. Backend Create Help code

- [x] 1.1 `StoreContentRequest`: `code` `required_if:kind,help` (valor enum Help),
      `nullable` si Article; `regex` `^[a-z0-9]+(?:[.\-][a-z0-9]+)*$`; max acorde
      columna; `unique` en `contents.code` con soft-deletes (withTrashed /
      Rule compatible); `prepareForValidation` lower+trim de `code` si viene.
- [x] 1.2 `CreateContent`: si kind Help → persistir `code` validado (sin
      `UniqueSlug`); si Article → `UniqueSlug::from(name)` como hoy; mantener
      Inactive, `company_id` null, forget HelpContentMap.
- [x] 1.3 Tests en `tests/Feature/Admin/ContentTest.php` (o archivo dedicado):
      Help con `companies.name` persiste exacto; Help sin code 422; code
      duplicado 422; formato inválido (`Companies Name`, espacios) 422;
      Article sin code sigue creando con slug.

## 2. Frontend Create

- [x] 2.1 `Create.tsx`: campo `code` visible solo si kind=help; required en
      validación cliente espejo; hint i18n con ejemplo `companies.nif`.
- [x] 2.2 Claves i18n es/en/ca (label código, hint convención, errores si
      aplica vía validation Laravel).

## 3. Docs

- [x] 3.1 Glosario / fila Contenidos en `docs/modules/Admin.md`: Create Help
      exige code explícito `{scope}.{field}`; Article auto-slug; no slugify
      Help.
- [x] 3.2 Pint dirty + suite Feature Contents afectada en verde.

## 4. Fuera de alcance (no implementar)

- Renombrar codes ya mal slugificados en BD.
- Code editable en Edit o en Article Create.
- E2E Pest browser (declarado N/A en design).
