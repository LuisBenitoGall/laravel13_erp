# Design

## Context

Create Content hoy pide solo `name` + `kind`. `CreateContent` deriva `code` con
`UniqueSlug::from($name)` → `Str::slug`, incompatible con anclas HelpTip
`{helpScope}.{fieldKey}` (punto). Edit ya muestra `code` como identificador
inmutable. Ver proposal.md — Why.

Convención de ancla (helptip-auto-code / glosario): `companies.nif`,
`companies.name`. Códigos legacy con guión (`contents-admin`) deben seguir
siendo válidos al crear Help si se teclean así.

E2E Pest browser: **no aplica** en este change (coverage Feature HTTP del Store).

## Goals / Non-Goals

**Goals:**

- Create Help: campo `code` obligatorio, persistido sin slugify.
- Create Article: sin campo code; `UniqueSlug` desde nombre (status quo).
- Validar formato + unicidad (withTrashed); normalizar a minúsculas.
- UI: mostrar/ocultar el campo según kind; i18n.

**Non-Goals:**

- Cambiar Edit (code inmutable).
- Migrar/renombrar codes mal generados (`companies-name` → `companies.name`).
- Autocompletar code desde name.
- Cambiar HelpTip / mapa / export-help (salvo docs del ritual Create).
- Code editable para Article.

## Decisions

### D1 — Code explícito solo si kind=Help

- **Elegido:** En Create, si Informacion → input `code` required. Si Articulo →
  no se envía `code`; Action usa `UniqueSlug` como ahora.
- **Alternativa descartada:** Code siempre manual para ambos tipos — Article no
  necesita anclas con punto; más fricción sin beneficio HelpTip.
- **Alternativa descartada:** Solo “preservar puntos” en UniqueSlug — el nombre
  humano (`NIF de empresa`) no es el code; mezclaría conceptos.

### D2 — Formato de code Help

- **Elegido:** Tras `Str::lower` + trim: regex
  `^[a-z0-9]+(?:[.\-][a-z0-9]+)*$`, max 64 (o el max de columna actual si es
  menor). Permite `companies.nif` y `contents-admin`. Rechaza espacios, `_`,
  mayúsculas sin normalizar, puntos consecutivos, leading/trailing `.`/`-`.
- **Alternativa descartada:** Solo `scope.field` con punto obligatorio —
  rompería crear tips legacy con guión.
- Unicidad: `Rule::unique('contents', 'code')->whereNull(...)` o query
  `Content::withTrashed()->where('code', $code)->exists()` alineada a
  UniqueSlug.

### D3 — Frontend Create

- Radio/select kind controla visibilidad del Field `code`.
- Nombre sigue siendo etiqueta humana (independiente del code).
- Hint i18n: ejemplo `companies.nif` / convención helpScope.fieldKey.
- Client + `StoreContentRequest` espejo (required_if kind=help).

### D4 — Action

```
if Help: use validated code (lowercased); assert !exists
if Article: UniqueSlug::from(name, ...)
```

Invalidar `HelpContentMap` como hoy.

## Risks / Trade-offs

- [Actor escribe `companies-name` por costumbre] → Mitigation: hint + docs;
  validación no “corrige” a punto.
- [Duplicar code Active/Inactive/trashed] → Mitigation: unique withTrashed +
  mensaje claro.
- [Article path regress] → Mitigation: test Create Article sin code sigue OK.

## Migration Plan

- Deploy solo código; sin migración BD.
- Rollback: revert PR; Contents Help creados con punto siguen válidos en BD.
- Códigos erróneos previos: limpieza manual (fuera de alcance).

## Open Questions

_(ninguna — decisión Help-only tomada en propose; si Luis quiere code manual
también en Article, ampliar en follow-up)._
