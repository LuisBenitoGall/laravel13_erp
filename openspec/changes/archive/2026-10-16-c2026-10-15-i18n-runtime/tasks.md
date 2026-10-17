## 1. Datos y config

- [x] 1.1 Migración: columna `users.locale` (string, nullable)
- [x] 1.2 `User`: fillable/casts; factory opcional con locale
- [x] 1.3 Config de locales soportados (`es`,`en`,`ca`) + default `es`
- [x] 1.4 Crear `lang/ca.json`; reordenar A–Z y paridad de claves en es/en/ca (claves ya existentes)

## 2. Runtime backend

- [x] 2.1 Middleware `SetLocale` (prioridad: user.locale → session → es)
- [x] 2.2 Registrar middleware en el stack web
- [x] 2.3 Ruta/action para cambiar idioma (guest → session; auth → `users.locale` + session)
- [x] 2.4 FormRequest/validación `locale` ∈ soportados
- [x] 2.5 `HandleInertiaRequests`: compartir `locale` (+ diccionario de traducciones del locale activo)

## 3. Runtime frontend

- [x] 3.1 Instalar `i18next` + `react-i18next` (canon `libraries.md`)
- [x] 3.2 Bootstrap i18n en el entry Inertia (locale + resources desde props compartidas)
- [x] 3.3 Componente `LanguageSwitcher` reutilizable
- [x] 3.4 Montar selector en Auth (guest) y en shell autenticado (layout/topbar existente)

## 4. Migración UI — Auth & Legal & shell

- [x] 4.1 Migrar `Pages/Auth/*` a `t()` / claves (Login, Register, Forgot, Reset, Onboard, SelectCompany)
- [x] 4.2 Migrar `Pages/Legal/*`
- [x] 4.3 Migrar `Dashboard.tsx` y `Welcome.tsx`
- [x] 4.4 Sustituir flashes/`__()` de Auth/onboarding visibles por claves presentes en los tres JSON

## 5. Migración UI — Partners & Admin

- [x] 5.1 Migrar `Pages/Partners/**` (Index/Create/Edit/Members + CompanyForm)
- [x] 5.2 Migrar `Pages/Admin/**` (Modules, Functionalities, CompanyModules, Authorizations + forms)
- [x] 5.3 Asegurar mensajes de dominio Partners/Admin en es/en/ca ordenados

## 6. Canon, agentes, CI

- [x] 6.1 Reforzar proceso i18n en `docs/standards/frontend.md` (y playbook si hace falta una línea)
- [x] 6.2 Cursor rule i18n (cero literales; claves en todos los locales; orden A–Z)
- [x] 6.3 Test Architecture: paridad de claves es/en/ca
- [x] 6.4 Test Architecture: claves ordenadas alfabéticamente en cada JSON
- [x] 6.5 Test Architecture: cada locale de config tiene fichero `lang/{locale}.json`
- [x] 6.6 Ficha módulo / V-01..V-20 / module-map / R-TEN-05 entidad nueva: no aplican (design)
- [x] 6.7 E2E rol 40 nuevo: no aplica
  - [x] Ajuste Browser Auth: no requerido — `php artisan test --testsuite=Browser --filter=Auth` 9/9 verde (locale default `es`; asserts literales siguen coincidiendo con `lang/es.json`)

## 7. Tests funcionales y calidad

- [x] 7.1 Feature: guest cambia idioma → session + UI locale
- [x] 7.2 Feature: auth cambia idioma → `users.locale` persistido
- [x] 7.3 Feature: default `es` sin preferencia
- [x] 7.4 Ajustar tests que aserten textos literales
- [x] 7.5 Pint / PHPStan / lint TS en tocados
- [x] 7.6 Suite relevante verde (Architecture + Feature i18n + humo Auth)
