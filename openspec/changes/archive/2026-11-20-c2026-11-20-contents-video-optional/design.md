# Design

## Context

En `UpdateContentRequest`, para Article se sobrescribe:

```php
$rules['video'] = ['required', 'array'];
```

En Laravel, `required` sobre array **falla con `[]`**. El front envía
`compactList` → `[]` cuando no hay URLs → mensaje «obligatorio».
`referrer` no tiene ese override. Motivación: proposal.md — Why.

## Goals / Non-Goals

**Goals:**

- `video` nunca required (Help ni Article).
- Spec explícito; test de regresión Article sin vídeo.

**Non-Goals:**

- Cambiar forma multi-valor / orden del form / referrer (salvo no
  tocar su opcionalidad).
- Validación de plataformas de vídeo; galería; Wysiwyg.

## Decisions

### D1 — Quitar el override `required` de video en Article

- **Decisión:** eliminar la línea que pone `video` → `required|array`
  cuando `$isArticle`. Dejar las reglas base:
  `sometimes|nullable|array` + `video.*` nullable url max:255.
  Mantener `content` required para Article (sin cambio).
- **Por qué:** root cause exacta del bug; mínimo diff.
- **Alternativa:** `present|array` — innecesario; el form ya envía el
  campo compactado.

### D2 — Spec normativo

- **Decisión:** en `contents-edit` / Update según kind, añadir que
  `video` SHALL ser opcional y SHALL NOT exigirse no vacío (incluido
  Article); vacío = `[]` sigue válido. Scenario: guardar Article sin
  vídeo.
- **Por qué:** pedido explícito «que quede en el spec».

### D3 — E2E / vinculaciones

- E2E: N/A (Feature HTTP basta).
- V-01..V-20: todos no.

## Risks / Trade-offs

- [Filas vacías `['']` sin compact] → Mitigación: el form ya hace
  `compactList`; `video.*` nullable evita castigar strings vacíos si
  llegaran.

## Migration Plan

1. Fix Request + Feature test.
2. Delta spec + ficha si aplica.
3. Pint / tests afectados.

Rollback: revert; sin migración BD.

## Open Questions

_(ninguna)_
