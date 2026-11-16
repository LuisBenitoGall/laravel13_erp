# Design

## Context

v1: `preferences` (name, url, user_id, module_id); topbar form; dashboard
CTAs con color/icon de Module. v2: `FavoritesStub` no-op; Dashboard
vacío; rutas bajo `/admin/...`. Brief: +`company_id`, path sin dominio,
tope 20, CTA color módulo.

## Goals / Non-Goals

**Goals:** Preference tenant+user; popover topbar; dashboard CTAs;
resolver `module_id`; tests aislamiento/ownership.

**NonGoals:** host en url; favoritos solo-user; notificaciones/notas/chat;
product favorites.

**E2E:** no aplica (§4 no lista este flujo).

**Excepciones:** ninguna.

## Decisions

### D1 — Modelo

```
preferences:
  id, name, url (string path+query, max 2048),
  user_id FK users,
  company_id FK companies,
  module_id FK modules nullable,
  timestamps
  index (user_id, company_id)
```

- Trait `BelongsToCompany` + scope sesión; create rellena `company_id`.
- Ownership: `user_id` = auth user (Policy).
- `url`: normalizar a path absoluto app (`/admin/...`) sin scheme/host;
  conservar query string; sin hash.
- Unique opcional `(user_id, company_id, url)` — **sí** (un path = un
  favorito; re-guardar actualiza name o 422).

### D2 — Límite 20

- En Create Action: si count ≥ 20 para (user, company) → 422 + mensaje
  i18n.
- Constante `Preference::MAX_PER_COMPANY = 20`.

### D3 — Resolver `module_id`

- Al guardar: dado el path, buscar en el menú efectivo / Functionalities
  con `href` conocido el **longest prefix match** → `functionality.module_id`.
- Si no hay match: `module_id = null` → CTA color slate neutro (sin
  inventar Module).
- Alternativa descartada: parsear segmento `[4]` como v1 (rompe `/admin`).

### D4 — Authz

- Sin Functionality de menú nueva (no es ítem lateral).
- Policy: `create` = autenticado + company sesión; `delete`/`view` =
  dueño + mismo company (o BelongsToCompany + user_id).
- Rutas: `POST /admin/preferences`, `DELETE /admin/preferences/{preference}`.

### D5 — UI

- Topbar: popover anclado (mismo patrón notificaciones); título «Mis
  opciones preferidas»; input name; hint; submit Inertia/fetch.
- Path enviado desde cliente: `window.location.pathname + search` (o
  `usePage().url` normalizado).
- Dashboard: props `preferences[]` { id, name, url, color, icon };
  grid CTAs; Link Inertia; botón delete (confirm ligero o X como v1).

### D6 — V-07 / V-08

- V-07: Policy sí; Spatie Functionality **no** (personal).
- V-08: no nuevo ítem menú lateral.
- R-TEN-05: favoritos company A no visibles en sesión B; destroy cross
  company 403/404.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| module_id null frecuente | Longest prefix + neutro OK |
| Path con id de otra company | Al navegar Policy destino; al listar solo sesión |
| Tope 20 sin UX clear | Mensaje explícito en popover |

## Migration Plan

1. Migración + modelo.
2. Store/destroy + resolver module.
3. Topbar + Dashboard.
4. Tests + docs.

## Open Questions

Ninguna.
