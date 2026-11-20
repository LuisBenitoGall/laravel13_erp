# Design

## Context

See `proposal.md` — Why. Hoy: `Field.helpCode` + NIF `company-nif`.
Brief: opción B auto-derive `{helpScope}.{fieldKey}`.

## Goals / Non-Goals

**Goals:** helpScope + fieldKey; override helpCode; migrar NIF;
documentar.

**Non-Goals:** Article tips; autogenerar Contents; manifiesto JSON;
seed masivo v1.

**E2E:** no aplica.

**Excepciones:** ninguna.

## Decisions

### D1 — Fórmula

```
code = helpScope + '.' + fieldKey
// companies.nif
```

- `helpScope`: slug estable del form (Functionality/`companies`).
- `fieldKey`: kebab-case del campo (`nif`, `featured`).

### D2 — API React

- `FormHelpScope` (React context) con `value: string` envuelve el form.
- `Field`:
  1. Si `helpCode` → usar tal cual (override).
  2. Else si context `helpScope` y `fieldKey` resoluble →
     `code = `${helpScope}.${fieldKey}``.
  3. Else → sin HelpTip.
- Resolución `fieldKey` (orden):
  1. prop opcional `fieldKey`
  2. prop `name` (si se añade / FormField)
  3. normalizar `htmlFor`: segmento tras el **último** `-`
     (`company-nif` → `nif`; `nif` → `nif`).
- Alternativa descartada: exigir `helpCode` siempre.

### D3 — CompanyForm

- Envolver con `FormHelpScope value="companies"`.
- Quitar `helpCode="company-nif"` del NIF (pasa a derive
  `companies.nif`).
- Asegurar `htmlFor`/`id` permiten fieldKey `nif` (hoy
  `company-nif` → ok vía último segmento).

### D4 — Migración company-nif → companies.nif

1. Fixture + seeder: code `companies.nif` (mismos textos).
2. One-shot: si existe fila `company-nif`, renombrar code a
   `companies.nif` (o upsert nuevo + soft-delete/alias).
3. **Alias temporal** en lookup HelpTip/mapa: si piden
   `companies.nif` y solo existe legacy… mejor rename limpio +
   alias inverso en mapa: al construir `help_tips`, duplicar entrada
   `company-nif` → mismo payload que `companies.nif` **una release**
   por si queda algún helpCode hardcodeado; luego quitar alias.

### D5 — Docs

- `frontend.md`: helpScope, fieldKey, override, ejemplos.
- Glosario: code Help = `{scope}.{field}`.

### D6 — Vinculaciones

V-* no; R-TEN-05 N/A.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| htmlFor ambiguo (`foo-bar-baz`) | prop `fieldKey` explícita |
| Colisión scopes | documentar slugs canónicos por form |

## Migration Plan

1. FormHelpScope + Field resolve.
2. CompanyForm + migrate code/fixture.
3. Docs + tests (derive, override, NIF).

## Open Questions

Ninguna.
