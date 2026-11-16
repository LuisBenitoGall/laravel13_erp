# Design

## Context

See `proposal.md` — Why. Referencia v1: `Content` + `Helpers::info($code)`
sobre JSON regenerado; type 1=info / 2=contenido; `serialize()` i18n.
v2: TipTap `Form/Wysiwyg`, `LangTabs`, catálogos bajo `configuracion`.

## Goals / Non-Goals

**Goals:** Help por ancla; CRUD SA Configuración; cache mapa; HelpTip;
`company_id` null en MVP; soft delete; Spatie.

**Non-Goals:** Article/Page/Slot; galería `ContentImage`; JSON runtime;
overrides por tenant; import masivo v1; ventana `published_at`/`published_end`
(**diferida** — Help se gobierna con status Active/Inactive).

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna formal. Nota: `company_id` nullable
**sin** `BelongsToCompany` (MVP solo globales; trait cuando existan filas
tenant). R-TEN-05: N/A aislamiento de filas tenant; sí test “mismo tip
tras cambio de company”.

## Decisions

### D1 — Modelo

```
contents:
  id
  name string(150)          # label interno admin
  code string unique        # ancla; inmutable tras create
  kind string               # enum ContentKind::Help (MVP)
  company_id nullable FK    # MVP siempre null al escribir
  title JSON Spatie
  excerpt JSON Spatie       # tip corto
  content JSON Spatie       # HTML allowlist (Wysiwyg)
  referrer nullable string  # URL “leer más”
  video nullable string     # URL embed
  status string             # ContentStatus Active/Inactive
  softDeletes, timestamps
  index (kind, status), index (company_id)
```

- `code`: slug desde `name` en create (unique); Edit **no** cambia code.
- Alternativa descartada: type int 1/2 v1; serialize; JSON file runtime.

### D2 — Authz / menú

- Functionality `contents` bajo Module `configuracion` (AdminOnly).
- Permisos `contents.view|viewAny|create|update|delete` (+ Policy).
- Visibilidad menú = capas Configuración (SA hoy).
- Store/Update **fuerzan** `company_id = null` y `kind = Help`.

### D3 — CRUD UI

- Index `<ServerTable>`: name, code, status toggle, Acciones Edit/Delete.
- Create/Edit **páginas** (no modal): LangTabs + title/excerpt/content;
  content = `Form/Wysiwyg`; excerpt Textarea; referrer/video Input;
  status Switch. Rutas `/admin/contents`.
- Alternativa descartada: modales (Wysiwyg + 3 locales demasiado pesado).

### D4 — Resolución / cache

- `HelpContentMap` (o Action/Service): carga Help Active `company_id` null
  → mapa `code => { title, excerpt, content, referrer, video }` para un
  locale (fallback locale app).
- `Cache::remember('contents.help.map.'.$locale, …)`; forget en
  Store/Update/Destroy/toggle status.
- Shared Inertia (once o shared): `help_tips` = mapa del locale actual
  **o** subset; `<HelpTip code>` lookup O(1); code missing → render null.
- Alternativa descartada: JSON en `storage/`; query por icono; N+1.

### D5 — Popover UI

- `<HelpTip code="…" />`: icono info + Popover/Tooltip shadcn; title +
  excerpt HTML sanitizado; enlace referrer si hay. Video/body completo
  no obligatorio en el tip (excerpt + leer más); body disponible en mapa
  para usos futuros.

### D6 — Vinculaciones §6

| V | ¿Sí? | Nota |
|---|------|------|
| V-07 | Sí | Functionality + permisos |
| V-08 | Sí | Configuración → Contenidos |
| V-09 | No | Module `configuracion` existente |
| R-TEN-05 | N/A filas | Globales; test cross-company mismo tip |
| resto | No | — |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Mapa grande en shared props | Cache + once; más adelante subset por página |
| HTML en popover XSS | Allowlist al persistir (mismo patrón description) |
| Confusión con Note | Glosario Content ≠ Note |

## Migration Plan

1. Migración + enum + modelo + Functionality seed.
2. Actions/Policy/rutas + cache invalidation.
3. UI CRUD + HelpTip + shared map.
4. Docs + Features.

## Open Questions

Ninguna (published_* diferida explícitamente).
