# Design

## Context

v1: `notes` (user_id, title, note, status); modal topbar; Livewire
post-its en dashboard + delete. v2: `NotesStub` popover vacío; Dashboard
ya tiene favoritos. Brief: sin status ni company_id; modal; tope 50.

## Goals / Non-Goals

**Goals:** Note personal; modal alta; badge; post-its dashboard; tope 50.

**Non-Goals:** tenant; status; Index ServerTable; notificaciones/chat.

**E2E:** no aplica.

## Excepciones a standards

| Estándar | Excepción | Justificación |
|----------|-----------|---------------|
| shell-topbar: paneles stub/notas como popover anclado; no dialog centrado | Alta de notas = **Dialog/modal** centrado | Acordado en brief/explore (textarea); «Ver todas» → dashboard |

## Decisions

### D1 — Modelo

```
notes:
  id, user_id FK users,
  title string(150), note text,
  timestamps
  index (user_id)
```

- Sin `BelongsToCompany`. Sin `status`.
- SoftDeletes: **no** en MVP (delete hard como v1 listado); opcional
  soft más adelante.
- `Note::MAX_PER_USER = 50`.

### D2 — Authz

- Policy: create = auth; delete/view = `note.user_id === auth.id`.
- Sin Functionality de menú lateral.
- Rutas: `POST /admin/notes`, `DELETE /admin/notes/{note}`.

### D3 — UI topbar

- Sustituir `NotesStub`: trigger icono + badge (`notes_count` shared o
  prop layout).
- Click → Dialog (shadcn): tabs o cabecera «Nueva nota» + enlace
  «Ver todas» → `/dashboard`; fields title + note (Textarea); Guardar
  Inertia/fetch.
- Tras guardar: toast; cerrar modal; invalidar/reload si en dashboard
  para prepend post-it; actualizar badge.

### D4 — Dashboard

- Props `notes[]` { id, title, note, created_at } orden DESC.
- UI post-it (estilo ámbar/crema, sombra ligera; no “AI purple”).
- Delete con confirm ligero o icono trash → destroy + refresh.
- Sección independiente de favoritos.

### D5 — Shared badge

- `HandleInertiaRequests` (o equivalente): `notes_count` para user auth
  (cap visual 50).

### D6 — V-*

- V-07/V-08: no Functionality menú.
- R-TEN-05: N/A (no company); test ownership cross-user 403/404.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Confundir con DeliveryNote | Glosario + namespace Admin `Note` |
| Modal vs popover guideline | Excepción explícita arriba |

## Migration Plan

1. Migración + modelo.
2. Store/destroy + Policy.
3. Modal topbar + dashboard post-its + badge.
4. Tests + docs.

## Open Questions

Ninguna.
