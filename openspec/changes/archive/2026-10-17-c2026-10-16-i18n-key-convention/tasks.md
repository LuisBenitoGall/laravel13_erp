## 1. Estándares

- [x] 1.1 Actualizar `docs/standards/glossary.md`: clave UI en castellano (no inglés jerárquico)
- [x] 1.2 Actualizar R-FE-05 en `docs/standards/frontend.md` (genéricos + familias `_`)
- [x] 1.3 Actualizar R-TRV-05 / mención en `architecture.md` si cita el esquema viejo
- [x] 1.4 Actualizar `.cursor/rules/i18n.mdc` y ejemplos en `templates-frontend.md` si aplica
- [x] 1.5 Ficha módulo / V-01..V-20 / module-map / R-TEN-05: no aplican (design)

## 2. Mapa y diccionario

- [x] 2.1 Elaborar mapa old→new de todas las claves actuales según reglas del design
- [x] 2.2 Reescribir `lang/es.json`, `en.json`, `ca.json` (paridad, A–Z, sin claves viejas)
- [x] 2.3 Actualizar o alinear `scripts/generate-i18n-json.php` con el nuevo vocabulario

## 3. Migración de usos

- [x] 3.1 Sustituir `t('…')` en `resources/js/Pages/**` y Layouts/Components
- [x] 3.2 Sustituir `__('…')` en Controllers / PHP de producto del inventario
- [x] 3.3 Verificar que no quedan strings de clave con puntos en el inventario migrado

## 4. Tests y CI

- [x] 4.1 Ampliar Architecture: claves MUST `^[a-z0-9_]+$`
- [x] 4.2 Mantener tests de paridad y orden A–Z verdes
- [x] 4.3 Ajustar Feature/Browser si asertan claves u omiten textos por rename
- [x] 4.4 E2E rol 40 nuevo: no aplica; Browser Auth solo si fallan
  - Veredicto 40 (2026-10-17): PASS — `npm run build` + `php artisan test --testsuite=Browser` → 9 passed (0 failed). Sin ajustes de assert; sin suite E2E nueva.
- [x] 4.5 Pint / PHPStan / humo suite relevante verde
