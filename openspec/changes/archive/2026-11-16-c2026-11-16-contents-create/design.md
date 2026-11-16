# Design

## Context

Brief `c2026-11-16-contents-create`. Supersede: propose
`c2026-11-16-contents-help` (archivado `*-superseded`). v1 Create =
name + type + status checkbox → Continuar Edit. Luis: sin status en
Create; default Inactive; ambos tipos; post-create placeholder; HelpTip
sí; Edit real = otro change.

## Goals / Non-Goals

**Goals:** modelo + Index + Create mínimo + placeholder Edit + menú +
HelpTip/cache.

**Non-Goals:** Edit real por kind; galería; published_*; JSON runtime;
overrides tenant.

**E2E:** no aplica.

**Excepciones:** `company_id` nullable **sin** `BelongsToCompany` (solo
null en Store). R-TEN-05 N/A filas; test tip/global independiente de
company de sesión cuando aplique.

## Decisions

### D0 — Orphan contents-help

- Carpeta mal ubicada / propose previo → `openspec/changes/archive/…-contents-help-superseded*`.
- Contrato vigente = este change.
- Main ya tiene `openspec/specs/contents-help` + ítem menú Contenidos
  (sync prematuro). Al archivar Create, 30 reconciliará: deprecar o
  fusionar `contents-help` → `contents-create` sin duplicar menú.

### D1 — Modelo

```
contents:
  id
  name string(150)
  code string unique          # slug(name) en create; inmutable
  kind string                 # ContentKind: Help | Article
  company_id nullable FK      # Store fuerza null
  title JSON Spatie nullable  # vacío en Create
  excerpt JSON Spatie nullable
  content JSON Spatie nullable
  referrer nullable string    # Edit futuro
  video nullable string
  status string               # ContentStatus; Create → Inactive
  softDeletes, timestamps
```

- UI: Información = `Help`; Contenido = `Article`.
- Alternativa descartada: solo Help en Create; type int v1.

### D2 — Create / placeholder

- Create page: `name` + radio/select kind; submit Continuar.
- Store → Inactive + code + null company → redirect
  `contents.edit` placeholder.
- Placeholder: muestra name/code/kind + mensaje i18n “edición
  próximamente”; Volver al Index; **sin** LangTabs/Wysiwyg/status toggle
  (status queda Inactive hasta change Edit).
- Soft delete desde Index ( Acciones ) sí en este change (listado usable).

### D3 — Index / authz / menú

- ServerTable: name, code, kind, status (badge RO), Acciones
  Edit(placeholder)/Delete.
- Functionality `contents` bajo `configuracion`; permisos canónicos +
  Policy.
- Rutas: index, table, create, store, edit (GET placeholder), destroy.

### D4 — HelpTip / cache

- Service mapa: Help **Active** `company_id` null →
  `code => { title, excerpt, … }` por locale.
- Cache key `contents.help.map.{locale}`; forget en Store/Destroy
  (y más adelante Update/status).
- Shared/once `help_tips`; `<HelpTip code>` popover; missing/Inactive →
  null UI.
- Create no rellena textos → mapa vacío hasta Edit change.

### D5 — Vinculaciones §6

| V | ¿Sí? | Nota |
|---|------|------|
| V-07 | Sí | `contents.*` |
| V-08 | Sí | Configuración → Contenidos |
| V-09 | No | Module existente |
| R-TEN-05 | N/A | Globales |
| resto | No | — |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Placeholder frustra | Change Edit inmediato después; CTA claro |
| Article sin Edit | Permitido crear; no publicable hasta Edit |

## Migration Plan

1. Migración + seed Functionality.
2. Store/Index/Destroy + placeholder + HelpTip.
3. Docs + Features.

## Open Questions

Ninguna.
