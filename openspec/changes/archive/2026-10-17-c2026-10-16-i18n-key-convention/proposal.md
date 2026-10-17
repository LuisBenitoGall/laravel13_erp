## Why

Las claves i18n actuales (`admin.functionalities.form.name` → «Nombre») son más largas
que el valor, no aportan semántica útil y se duplican por pantalla. El ERP necesita
índices en castellano reutilizables (estilo v1 `textos.php`), con familias por prefijo,
para no inflar el diccionario en cada módulo.

## What Changes

- Convención nueva: clave UI en **castellano snake_case** (`nombre`, `usuario_nuevo`);
  genéricos reutilizados; específicas solo si el texto no es el genérico
  (`usuario_nombre`).
- Migración **completa** de `lang/{es,en,ca}.json` y de todos los `t()` / `__()` al
  nuevo esquema (sin convención mixta).
- Actualización de glosario, R-FE-05, rule `.cursor/rules/i18n.mdc` (y templates si
  citan el esquema viejo).
- Runtime i18n **sin cambios** (locales, selector, `users.locale`, CI de paridad/orden).
- CI reforzado: claves solo `[a-z0-9_]+` (sin puntos jerárquicos en inglés).

## Capabilities

### New Capabilities

- `i18n-key-convention`: reglas de nombrado castellano, genéricos, familias y migración.

### Modified Capabilities

- `i18n-runtime`: la forma de nombrar claves pasa a castellano snake_case; el runtime
  (locale/JSON/selector) se conserva.

## Impact

- `lang/es.json`, `en.json`, `ca.json` (reescribir índices)
- Todas las llamadas `t('…')` / `__('…')` en Pages, Layouts, Components, Controllers
- `docs/standards/glossary.md`, `frontend.md`, `.cursor/rules/i18n.mdc`
- `scripts/generate-i18n-json.php` (si se mantiene como ayuda)
- Tests Feature/Browser que aserten claves o textos vía i18n
- Suite Architecture i18n (ampliar regla de forma de clave)
