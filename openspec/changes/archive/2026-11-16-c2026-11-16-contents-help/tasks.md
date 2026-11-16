# Tasks

## 1. Docs + datos

- [x] 1.1 Glosario: Contenido / `Content`, ancla Help / `code`; ficha
      Admin (entidad + V-07/V-08); `module-map` Admin + Content.
      Verificar docs.
- [x] 1.2 Migración `contents` (D1) + enums `ContentKind`/`ContentStatus`
      + modelo Spatie + factory. Verificar `migrate`.
- [x] 1.3 Seed Functionality `contents` bajo `configuracion` + permisos
      canónicos. Verificar seeder/menú SA.

## 2. Backend

- [x] 2.1 Actions Store/Update/Destroy/Toggle + FormRequests (force
      `company_id` null, `kind` Help; code unique inmutable en update;
      HTML allowlist content). Verificar Features alta/update/delete.
- [x] 2.2 Policy + rutas `/admin/contents` (+ table, status). Verificar
      403 sin permiso.
- [x] 2.3 `HelpContentMap` + cache por locale + invalidación en mutate;
      shared/once `help_tips`. Verificar Feature: tras edit el mapa
      refleja cambio; sin lectura JSON runtime.
- [x] 2.4 Test mismo tip tras cambio de company (global). Verificar
      Feature.

## 3. Frontend

- [x] 3.1 Index ServerTable + Create/Edit páginas (LangTabs, Wysiwyg
      content, excerpt, referrer, video, status). Verificar UI authz.
- [x] 3.2 Componente `<HelpTip code />` (popover title+excerpt;
      silent si falta). Verificar montaje con code existente/inexistente.
- [x] 3.3 i18n es/en/ca + Pint + PHPStan + Features verdes.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Galería imágenes / kinds editorial / published_*: changes futuros.
