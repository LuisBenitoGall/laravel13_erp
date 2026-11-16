# Tasks

## 1. Datos + docs

- [x] 1.1 Migración `preferences` (name, url, user_id, company_id,
      module_id nullable, índices). Verificar `migrate`.
- [x] 1.2 Modelo `Preference` + `BelongsToCompany` + factory + constante
      MAX 20. Verificar factory.
- [x] 1.3 Glosario Favorito/`Preference` + nota Admin/shell. Verificar
      docs.

## 2. Backend

- [x] 2.1 Resolver `module_id` (longest prefix menú/Functionality href).
      Verificar unit/Feature con path de ejemplo.
- [x] 2.2 Action Store: normalizar path sin host; tope 20; unique url por
      user+company. Verificar Feature (alta, tope, duplicate).
- [x] 2.3 Action Destroy + Policy ownership/tenant. Verificar 403/404
      cruzados (R-TEN-05 / dueño).
- [x] 2.4 Rutas `admin.preferences` store/destroy; Dashboard props
      preferences. Verificar Feature.

## 3. Frontend

- [x] 3.1 Sustituir `FavoritesStub` por popover real (nombre + guardar).
      Verificar UI/source.
- [x] 3.2 Dashboard: sección CTAs color/icon Module + delete. Verificar
      UI.
- [x] 3.3 i18n es/en/ca + Pint + PHPStan + Features verdes.

## 4. Correcciones 25 (open-redirect + drift)

- [x] 4.1 `PreferenceUrlNormalizer::forStorage` — rechazar `//` y paths
      fuera de `/admin|/dashboard|/account`; ValidationException i18n.
- [x] 4.2 Documentar url ≤760 (migración + Admin.md) — opción A.
- [x] 4.3 Single source `MenuFunctionalityHref` (MenuBuilder + resolver).
- [x] 4.4 Feature: `//evil`, `/logout` 422; https→path OK; assert `//`.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → **25-architecture-review** → 30 archive.
- E2E 40: no aplica.

