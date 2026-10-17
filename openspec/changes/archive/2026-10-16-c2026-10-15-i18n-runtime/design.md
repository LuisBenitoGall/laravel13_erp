## Context

Canon ya nombra es/en/ca y `i18next`+`react-i18next`. Repo: solo `lang/es.json` +
`en.json` (desordenados), sin `ca`, sin paquetes i18n en `package.json`, páginas con
literales. Brief: un JSON por locale, migrar toda UI existente, selector, preferencia en
`users`, listo para idiomas/textos futuros.

Ficha de módulo / V-01..V-20 / module-map: **no aplican** (transversal i18n).

## Goals / Non-Goals

**Goals:**

- Runtime es (default) / en / ca; JSON únicos por locale; claves ordenadas A–Z.
- Columna `users.locale`; selector UI; migración inventario completo de Pages + UI PHP.
- CI + Cursor rule + refuerzo en standards para el proceso futuro.

**Non-Goals:**

- JSON partidos por dominio.
- Preferencia solo sesión/navegador (sesión solo como puente guest).
- Traducir contenidos legales definitivos (placeholders siguen siendo i18n keys).
- E2E nuevo obligatorio (`e2e.md` §4 no lista i18n); Feature + Architecture bastan.
- Cambiar reglas de negocio Auth/tenancy.

## Decisions

1. **Columna `users.locale`**  
   `string` nullable, valores `es`|`en`|`ca` (validación `Rule::in`). `null` = default
   `es`. Nombre canónico `locale` (glosario/Laravel).  
   _Descartado:_ `language` / `preferred_locale` más verboso sin ganancia.

2. **Config de locales soportados**  
   Lista en config (p. ej. `config/i18n.php` o `app.supported_locales`) =
   `['es','en','ca']`, `fallback`/`default` = `es`. Añadir idioma futuro = ampliar lista +
   nuevo `lang/{code}.json` + UI del selector.

3. **Resolución de locale (prioridad)**  
   ```
   User autenticado con locale válido
     → else session('locale') válido
     → else default es
   ```
   Middleware `SetLocale` (o equivalente) llama `App::setLocale` en cada request.
   Guest: el selector escribe solo sesión. Login: si `user.locale` es null, se puede
   adoptar el de sesión una vez (opcional en apply); si tiene locale, manda el del User.
   Autenticado: selector actualiza `users.locale` + sesión.

4. **Fuente única de textos = `lang/{locale}.json`**  
   PHP: `__('key')` / `Lang::get` sobre JSON Laravel.  
   React: `i18next` + `react-i18next`; recursos cargados desde esos JSON (import Vite o
   prop Inertia `translations` + `locale` compartidos en `HandleInertiaRequests`).  
   Preferencia de implementación: **compartir por Inertia** el diccionario del locale
   activo (misma fuente que PHP, sin duplicar árbol `resources/js/locales/`).  
   _Descartado:_ mantener dos árboles de traducción.

5. **Claves**  
   Estilo existente / glosario: `dominio.entidad.accion_o_campo` en inglés
   (`auth.login.title`, `partners.companies.created`). Orden **alfabético de clave** en
   cada fichero (CI lo exige).

6. **Selector**  
   Componente reutilizable (p. ej. `LanguageSwitcher`) en Auth (todas las pantallas
   guest/auth relevantes) y en shell autenticado (topbar / layout existente o el punto
   estable que haya hoy). POST/PATCH ruta dedicada o Inertia visit que persiste según
   Decisión 3.

7. **Inventario a migrar (completo)**  
   `Pages/Auth/*`, `Legal/*`, `Dashboard`, `Welcome`, `Partners/**`, `Admin/**` (incl.
   forms en `components/`), más flashes/`__()` de producto ya usados. Checkbox/file no
   son textos; labels sí.

8. **Enforcement futuro**  
   - Cursor rule i18n (R-FE-05 / R-TRV-05): cero literales; claves en es+en+ca; orden A–Z.  
   - Pest Architecture: (a) mismas claves en es/en/ca; (b) claves ordenadas en cada JSON;
     (c) locales de config ⊆ ficheros existentes.  
   - Refuerzo breve en `frontend.md` / playbook i18n (proceso al añadir texto).  
   _Skill aparte:_ no obligatorio si la rule + CI cubren el brief (“rule/skill/CI”).

9. **E2E**  
   No aplica task rol 40 nueva. Browser Auth existentes: seguirán si usan labels/roles;
   ajustar solo si rompen por i18n.

## Risks / Trade-offs

- [Inventario grande de literales] → Mitigación: tasks por área (Auth → Legal → shell →
  Partners → Admin); checklist en tasks.
- [Guest vs User locale] → Mitigación: prioridad explícita (Decisión 3) + tests.
- [JSON monolíticos al crecer] → Aceptado por brief (un fichero por locale); split futuro
  sería otro change.

## Migration Plan

- Migración BD `locale` nullable; backfill no necesario (`null` → es).
- Deploy: código + JSON + npm. Rollback: revertir; columna puede quedar nullable sin uso.

## Open Questions

- Ninguna bloqueante (cerradas en explore + este design).
