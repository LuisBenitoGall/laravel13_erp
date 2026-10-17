## Why

El canon (R-TRV-05 / R-FE-05) exige UI multiidioma sin literales, pero el runtime no está
cableado: faltan `ca`, `i18next`/`react-i18next`, preferencia de usuario y las páginas
existentes llevan castellano hardcodeado. Hay que materializar el contrato i18n ahora
para que todo desarrollo futuro nazca ya con claves.

## What Changes

- Runtime: idiomas **es** (default), **en**, **ca**; `lang/ca.json`; claves A–Z en cada
  `lang/{locale}.json`.
- Preferencia en `users.locale`; selector de idioma en UI (guest + autenticado).
- Cableado React con `i18next` + `react-i18next` (canon `libraries.md`); PHP/`__()`
  alineado al mismo JSON.
- Migración de **toda** la UI ya generada (Auth, Legal, Dashboard, Welcome, Partners,
  Admin, flashes/mensajes de producto) a claves i18n.
- Norma + Cursor rule + CI (paridad de claves, orden alfabético) para bloques futuros e
  idiomas nuevos.
- **No** partir JSON por dominio (un fichero por locale).

## Capabilities

### New Capabilities

- `i18n-runtime`: locales, ficheros JSON, preferencia User, selector, cableado front/back,
  migración UI existente, enforcement CI/rule.

### Modified Capabilities

- (ninguna spec de dominio reabierta; Auth/Partners siguen iguales en negocio — solo textos)

## Impact

- Migración `users.locale`; `User` fillable/casts; factory
- Middleware / `HandleInertiaRequests` (locale + diccionario)
- `npm`: `i18next`, `react-i18next`
- `lang/es.json`, `en.json`, `ca.json` (ordenar + completar)
- Páginas bajo `resources/js/Pages/**` y mensajes `__()` / flashes
- `docs/standards/frontend.md` (refuerzo proceso), Cursor rule i18n
- Tests Feature (preferencia/selector) + Architecture (JSON)
