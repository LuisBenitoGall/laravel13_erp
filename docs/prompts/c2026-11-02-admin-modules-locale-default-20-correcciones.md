Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Origen: hallazgos MEDIUM de 25-architecture-review (Create / Edit / Functionalities
anidadas): el front marca `required` del LangTabs con `locale === 'es'` hardcodeado,
mientras el backend usa `LocaleResolver::default()` (`config('i18n.default')`).

Objetivo: **cero literales de locale de negocio en el front** para “idioma principal
required”. La UI debe usar la misma fuente que el servidor.

## Regla

- Prohibido `locale === 'es'`, `locale === "es"`, o cualquier comparación fija a un
  código de idioma para decidir required / validación de traducciones.
- Prohibido fallbacks del tipo `defaultLocale ?? 'es'` o `supportedLocales ?? ['es', …]`
  en esas páginas para este criterio (el shared Inertia debe traer el valor).
- El locale principal = `LocaleResolver::default()` en PHP = lo que ya usan
  `StoreModuleRequest`, `UpdateModuleRequest`, `StoreFunctionalityRequest`,
  `UpdateFunctionalityRequest`.

## Qué hacer

### 1. Shared Inertia

En `HandleInertiaRequests`, compartir el locale por defecto del proyecto junto a
`supportedLocales`, p. ej. `defaultLocale` => `app(LocaleResolver::class)->default()`.

Actualizar `resources/js/types/i18n.d.ts` (`I18nSharedProps`) para tipar
`defaultLocale: string`.

### 2. Pages Modules

Sustituir todos los `required={locale === 'es'}` por comparación al
`defaultLocale` shared (`usePage().props.defaultLocale`):

- `resources/js/Pages/Admin/Modules/Create.tsx` (label + explanation)
- `resources/js/Pages/Admin/Modules/Edit.tsx` (tab Módulo label/explanation + modal
  Create Functionality label + modal Edit Functionality label)

Leer `defaultLocale` del page props; no inventar otro canal (env, i18n.language de
sesión del usuario ≠ locale principal del catálogo).

### 3. Barrido

Buscar en `resources/js` otras apariciones de `locale === 'es'` (o equivalente) usadas
para required de traducciones. Si aparecen en Create/Edit Modules o modales de
Functionalities anidadas, corregirlas. No migrar formularios ajenos fuera de Modules
salvo que tengan el mismo hardcode de “principal = es”.

### 4. Tests

- Aserción de fuente: Create y Edit **no** contienen `locale === 'es'` /
  `locale === "es"`.
- Contienen uso de `defaultLocale` (prop / comparación).
- Feature existente de store/update Modules y `ModuleFunctionalitiesTest` siguen
  verdes (el backend no cambia la regla; solo se alinea el UI).

Opcional y preferible: un test Feature/Inertia o de middleware que el shared
incluya `defaultLocale` igual a `config('i18n.default')` (sin hardcodear el valor
esperado en el assert: leer `config('i18n.default')` en el test).

## No tocar

- Lógica de `LocaleResolver` / `config/i18n.php` salvo leer `default()`.
- Reglas de FormRequest (ya correctas).
- Otros hallazgos 25 (rutas genéricas Functionalities, tamaño de Edit.tsx, etc.).
- No archivar. No Browser E2E.

## Gate

- Diff sin literales `'es'` / `"es"` en comparaciones de locale required en
  Create/Edit Modules.
- `defaultLocale` en shared + tipos TS.
- Tests del punto 4 en verde.
- Pint solo si hubo PHP (`HandleInertiaRequests`).

## Contexto de change

Corrección transversal de UI Modules (hallazgos Create + Edit + Functionalities).
Si hay un change OpenSpec activo de Modules, anota una subtarea breve en su
`tasks.md` y márcala al cerrar. Si no hay change abierto, aplica el fix en código;
no inventes proposal/design nuevos.
