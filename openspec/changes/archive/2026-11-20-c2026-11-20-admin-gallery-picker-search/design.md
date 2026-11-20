# Design

## Context

Hoy `picker()` autoriza `viewAny`, aplica el mismo scope de visibilidad
que Index y devuelve **todos** los assets como `{ assets: [...] }`.
`GalleryInsertDialog` hace un `fetch` al abrir y pinta el grid completo.
Motivación: ver proposal.md — Why. Criterio de producto: solución
económica y contrato reutilizable por Fase 3.

## Goals / Non-Goals

**Goals:**

- Paginación + búsqueda server-side en el endpoint picker.
- UX del modal: debounce, infinite scroll, reset/cancelación de races.
- Contrato JSON estable para futuros consumidores (sin extraer componente).

**Non-Goals:**

- `<MediaPicker>` genérico; Index Galería; upload empresa; thumbnails.

## Decisions

### D1 — Query params y defaults

- **Decisión:** `q` opcional (trim; vacío = sin filtro). `page` default 1.
  `per_page` default **24**, máximo **48** (valores fuera de rango → clamp
  o validación 422; preferir clamp silencioso para UX del modal).
- **Por qué:** 24 ≈ 6 filas × 4 cols en el grid actual; 48 tope evita
  abusos. Alternativa cursor: overkill vs Laravel `paginate`.

### D2 — Forma de respuesta

- **Decisión:**
  ```json
  {
    "assets": [{ "id", "name", "alt", "url" }],
    "meta": {
      "current_page": 1,
      "last_page": N,
      "per_page": 24,
      "total": T
    }
  }
  ```
  Implementación: `LengthAwarePaginator` sobre la query visible; mapear
  ítems al shape actual (no Resource Inertia completo).
- **Por qué:** brief; suficiente para infinite scroll (`current_page <
  last_page` → fetch `page+1`). No mezclar con envelope Spatie/JSON:API.

### D3 — Filtro `q`

- **Decisión:** `where` LIKE case-insensitive sobre `name` OR `alt`
  (bindings; sin raw user SQL). Orden estable: `id` desc (igual que Index).
- **Por qué:** económico; índices fulltext fuera de alcance.

### D4 — Front: debounce + AbortController + append/replace

- **Decisión:** input controlado; debounce ~300 ms → fetch page 1
  **replace**. Sentinel (`IntersectionObserver`) al final del scroll del
  **grid del dialog** (no `window`) → si hay más páginas y no loading,
  fetch `page+1` y **append**. Abort al cambiar `q`, al cerrar, o al
  disparar un fetch nuevo. Ignorar respuestas abortadas.
- **Por qué:** evita el bug clásico de append cruzado entre búsquedas.
- **Alternativa «Cargar más»:** más barata pero peor UX; brief eligió
  infinite scroll una vez existe la API.

### D5 — Compatibilidad breaking del picker

- **Decisión:** breaking aceptado: deja de devolver la lista completa sin
  `meta`. Único consumidor hoy: `GalleryInsertDialog` (actualizar en el
  mismo change). Tests Feature del picker se reescriben al nuevo contrato.
- **Por qué:** no hay API pública externa; Fase 3 aún no existe.

### D6 — E2E / vinculaciones

- E2E Pest browser: **no aplica**.
- V-01..V-20: todos no (sin permisos/menú/módulo nuevos). R-TEN-05: el
  perímetro del picker **no cambia**; mantener/ajustar test de aislamiento
  existente al shape paginado (sigue sin ver assets company B).

### D7 — Excepciones a standards

Ninguna.

## Risks / Trade-offs

- [LIKE `%q%` lento con miles] → Mitigación: tope `per_page`; aceptar hasta
  que haya volumen real; fulltext = change futuro.
- [Double-fetch en IntersectionObserver] → Mitigación: flag `loading` +
  abort + no observar si `page >= last_page`.
- [Breaking picker] → Mitigación: front + tests en el mismo PR.

## Migration Plan

1. Backend picker + Feature tests.
2. `GalleryInsertDialog` + i18n.
3. Pint / PHPStan / tests afectados.
4. Nota en ficha Admin (Fase 2b).

Rollback: revert; no migración BD.

## Open Questions

_(ninguna)_
