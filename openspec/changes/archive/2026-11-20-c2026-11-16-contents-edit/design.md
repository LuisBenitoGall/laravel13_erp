# Design

## Context

See `proposal.md` — Why. Estado: Create → placeholder Edit; Index con
toggle status (corrección). Canon UI: `RecordSummary` +
`auditRecordSummaryFields` (Workplaces/Modules). v1 Edit: kind fija
ramifica campos (Help = title+excerpt; Article = +content/tags…).

## Goals / Non-Goals

**Goals:** Edit real; cabecera título+historial+tipo RO; Update + cache;
ramificación Help vs Article.

**Non-Goals:** galería; published_*; tags/links/classes v1; cambiar
kind/code; Create/Index salvo invalidación cache ya prevista.

**E2E:** no aplica.

**Excepciones:** ninguna.

## Decisions

### D1 — Cabecera (RecordSummary)

- `titleKey` edición con `{name}` (o título = name del content).
- `identifier` = `code` (badge ámbar): **slug técnico inmutable** usado
  por HelpTip (`code`) y como clave estable del registro; no es el
  título UI ni es editable. Clarifica la ficha frente al name.
- `fields`: **tipo** (label i18n Información/Contenido, RO) +
  `auditRecordSummaryFields({ created_at, updated_at })`.
- Sin editar kind ni code en el form.
- Alternativa descartada: kind como Select disabled en el form (menos
  visible que en el summary junto al historial).

### D2 — Campos por kind y orden del form

Orden UI (Luis): **name + status → referrer/video (multi) → LangTabs
(traducciones al final)**. Evita confundir referrer/video con campos
traducibles.

| Campo | Help | Article |
|-------|------|---------|
| name | sí | sí |
| status | sí (Switch; además Index toggle) | sí |
| referrer | sí (JSON `string[]`) | sí |
| video | opcional (JSON `string[]`) | sí |
| title (LangTabs) | sí | sí |
| excerpt (LangTabs Textarea) | sí | sí |
| content (LangTabs Wysiwyg) | no (como v1 tip) | sí |

- Allowlist HTML en `content` (mismo patrón description Company).
- Default locale required en title (y content si Article).
- `referrer` / `video`: columnas JSON array de strings (URLs/enlaces);
  UI lista add/remove; vacío = `[]`. HelpTip consume listas.

### D3 — Backend

- `UpdateContent` Action + FormRequest; force ignore kind/code/
  company_id en update.
- `HelpContentMap::forget()` tras update (y status ya en toggle).
- GET Edit props: name, code, kind, status, title/excerpt/content por
  locale, referrer[], video[], created_at, updated_at.
- Migración: `referrer`/`video` string → JSON (valores legacy →
  `[valor]` o `[]`).

### D5 — Eliminar desde Edit (Luis)

- Acciones superiores del Edit: botón `type: 'delete'` (ViewActionBar)
  con confirmación y `DELETE admin/contents/{id}` → Index (mismo soft
  delete que Index).

### D4 — Vinculaciones

| V | ¿Sí? | Nota |
|---|------|------|
| V-07/08/09 | No | Ya en Create |
| R-TEN-05 | N/A | Globales |
| resto | No | — |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Activar Help sin excerpt | Permitido; tip vacío usable; SA responsable |
| Article sin content | Validar content required en default locale |

## Migration Plan

1. Update Action + Request + controller.
2. Edit UI RecordSummary + form.
3. Features Update + kind/code inmutables.
4. Quitar placeholder i18n muerto.

## Open Questions

Ninguna (published_*/galería siguen fuera).
