# Tasks

## 1. Backend Update

- [x] 1.1 Action `UpdateContent` + FormRequest (name, status, Spatie
      title/excerpt/content según kind, referrer, video; ignore
      kind/code/company_id; allowlist content). Verificar Feature update
      Help y Article.
- [x] 1.2 Controller update + authorize; Edit props amplias
      (timestamps, traducciones). Verificar GET Edit + PUT.
- [x] 1.3 `HelpContentMap::forget()` en Update. Verificar Feature tip
      tras editar Active.

## 2. Frontend Edit

- [x] 2.1 `RecordSummary`: título, badge code, fields tipo RO +
      created_at/updated_at. Verificar UI cabecera.
- [x] 2.2 Form Edit: name, status; LangTabs title/excerpt; content
      Wysiwyg solo Article; referrer/video. Verificar ramificación kind.
- [x] 2.3 Quitar placeholder; Continuar/Index Edit → form real. i18n
      es/en/ca + Pint + PHPStan + Features verdes.

## 3. Corrección Luis — referrer/video JSON + orden form

- [x] 3.1 Migración `referrer`/`video` → JSON `string[]`; model cast;
      Create/Update/Request/HelpContentMap/HelpTip. Verificar Feature
      multi-valor + tip.
- [x] 3.2 Form: referrer/video lista add/remove **encima** de LangTabs;
      traducciones al final. Verificar orden UI + Features/Pint/PHPStan.

## 4. Corrección Luis — Eliminar en Edit

- [x] 4.1 Botón Eliminar en acciones superiores del Edit (`type:
      'delete'`, confirm + `router.delete`), patrón Modules/Roles.
      Verificar source + Feature destroy existente.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Galería / published_*: changes futuros.
