# Design

## Context

Company Edit tab Información hoy: name / tradename / nif / status (sin
traducciones). El kit Form tiene `Textarea` y `LangTabs`; `Wysiwyg` está
marcado pendiente en `frontend.md`. `@tiptap/react` figura en
`libraries.md` “por módulo” y no está instalado. Directorio ya existe;
consumo de estos textos = change futuro (brief).

## Goals / Non-Goals

**Goals**

- Persistencia traducible `excerpt` / `description` en `companies`.
- UI solo en Edit → Información (`LangTabs`).
- Kit `Form/Wysiwyg` reutilizable + tiptap instalado.
- Canon docs: `frontend.md`, `libraries.md`, glosario, Admin.md.

**Non-Goals**

- Card/Show del Directorio.
- Inputs en Create.
- Traducir `name` / `tradename`.
- Editor ad-hoc fuera de `Components/Form`.
- Nuevo paquete PHP de purificación (allowlist nativa).

## Decisions

### D1 — Columnas JSON + Spatie `HasTranslations`

- Migración: `excerpt` y `description` `json` nullable `->after('parent_id')`.
- `Company::$translatable = ['excerpt', 'description']` (name sigue string).
- Alternativa descartada: columnas `excerpt_es`… (rompe patrón Module/Sector).

### D2 — Validación

- `excerpt`: array opcional; por locale `nullable|string|max:1000`.
- `description`: array opcional; por locale `nullable|string|max:20000`
  (HTML).
- Create: sin reglas de estos campos (no se envían).
- Límite 1000 = **por locale** (texto del brief + multi-idioma).

### D3 — Sanitización HTML de `description`

- Al persistir (Action/Update): allowlist de tags alineada al StarterKit
  TipTap (`p`, `br`, `strong`, `em`, `ul`, `ol`, `li`, `h2`, `h3`, `a`
  con `href` http(s)/mailto). Sin dependencia Composer nueva.
- Alternativa descartada: guardar HTML crudo (XSS en Show futuro).

### D4 — `Form/Wysiwyg`

- Componente controlado `value` / `onChange` (HTML string), usable con
  `Field`; toolbar mínima (negrita, cursiva, listas, headings).
- Deps npm: `@tiptap/react`, `@tiptap/starter-kit` (y `@tiptap/pm` si lo
  exige el peer). Actualizar `libraries.md` (sacar de “por módulo” →
  instalado con el kit) y `frontend.md` (Wysiwyg implementado).

### D5 — UI Edit

- Solo `mode === 'edit'` en `CompanyForm` (o equivalente): bloque
  `LangTabs` con excerpt (`Textarea` maxLength 1000) + description
  (`Wysiwyg`).
- Create sin el bloque. Props iniciales desde traducciones Spatie.

### D6 — Authz / tenancy

- Sin Policy nueva: mismo `update` + perímetro de vínculo que el Edit
  actual. Company no es `BelongsToCompany`.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| XSS al renderizar description en Directorio | Sanitizar al guardar ya; Show futuro no confía en HTML crudo |
| Bundle TipTap | Solo StarterKit; sin extensiones extra |
| Mezclar kit + entidad en un change | Brief opción A explícita; tasks separan kit vs Company |

## Migration Plan

1. Migración columnas → modelo → Request/Update → Wysiwyg + Edit UI.
2. Rollback: drop columnas; revert npm si hace falta.

## Excepciones a standards

Ninguna (este change **cumple** el pendiente Wysiwyg del kit).

E2E rol 40: **no aplica**.

## Open Questions

Ninguna.