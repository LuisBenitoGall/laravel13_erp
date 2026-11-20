# Design

## Context

See `proposal.md` — Why. Infra: `HelpTip` + `HelpContentMap` + shared
`help_tips` (solo Help Active, `company_id` null). `Field` tiene `help`
como hint bajo el label. v1: `Helpers::info($code)` icono a la derecha.

## Goals / Non-Goals

**Goals:** `helpCode` en Field; NIF empresa; fixture+seeder+export Help.

**Non-Goals:** tips Article; dump masivo v1; rediseñar cache; galería.

**E2E:** no aplica.

**Excepciones:** ninguna.

## Decisions

### D1 — Field.helpCode (no sibling suelto)

- Prop `helpCode?: string` en `Field` (y `FormField` si aplica).
- Layout: fila `flex items-start gap-2` → children (grow) + `<HelpTip
  code={helpCode} />` a la derecha.
- Hint textual `help` sigue debajo; independiente.
- Alternativa descartada: solo wrap ad-hoc en CompanyForm (no escala).

### D2 — Visual HelpTip

- Icono info clickable (Popover); acercar a v1: círculo ámbar/`i` o
  Info lucide con tono amber (captura), sin bloquear el input.
- Si code no está en `help_tips` → `HelpTip` ya return null (sin hueco
  vacío preferible: no reservar espacio si null).

### D3 — company-nif

- `CompanyForm` (Create+Edit): `<Field … helpCode="company-nif">` en NIF.
- Contenido lo crea Luis en local; seeder incluye `company-nif` para
  entornos limpios (upsert por code).

### D4 — Promoción cross-env

```
database/data/contents_help.json   # source of truth versionada
ContentsHelpSeeder                 # upsert Help por code (kind Help,
                                   # company_id null; ES/EN/CA)
php artisan contents:export-help   # dump Active Help globales → JSON
                                   # (para refrescar fixture tras editar
                                   # en un entorno)
```

- Upsert por `code` (no borra Help ajenos al fichero).
- Alternativa descartada: solo copiar BD; solo seeder sin export.

### D5 — Vinculaciones

| V | ¿Sí? | Nota |
|---|------|------|
| V-07/08 | No | Sin Functionality nueva |
| resto | No | — |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Fixture desactualizado vs BD local | export-help + doc en tasks |
| Icono reserva hueco si tip null | HelpTip null → sin botón |

## Migration Plan

1. Field.helpCode + polish HelpTip.
2. CompanyForm NIF.
3. JSON + Seeder + export command + tests.
4. Docs frontend.md / Admin.

## Open Questions

Ninguna.
