# Design

## Context

See `proposal.md` — Why. Piezas actuales:

- `ModuleController@store` ya redirige a `admin.modules.edit` tras `CreateModule`.
- `StoreModuleRequest` exige `slug` del cliente + `label.es` required + `status` required.
- `ModuleForm` compartido Create/Edit: slug editable en create, inputs texto para
  icon/color, select status, label es/en sin LangTabs.
- Form-kit MVP **sin** LangTabs/ColorPicker/IconPicker (`frontend.md` los marca
  pendientes salvo Color/Icon nuevos).
- Locales: `LocaleResolver` + `supportedLocales` en Inertia shared;
  `config('i18n.default')` = `es`.
- E2E §4: **N/A** (CRUD, no flujo crítico listado).
- Ficha `docs/modules/Admin.md` existe; no módulo nuevo. V-01..V-20 de entidad nueva:
  **N/A**; sí actualizar catálogo UI en `frontend.md` (LangTabs + ColorPicker +
  IconPicker).

## Goals / Non-Goals

**Goals:**

- LangTabs + ColorPicker + IconPicker en `Components/Form`.
- Create Module alineado al contrato (slug server-side, CI unique, traducciones).
- Separar Create del Edit MVP lo bastante para no arrastrar campos fuera de alcance
  en Create (Edit puede seguir temporalmente con el form antiguo hasta su change).

**Non-Goals:**

- Edit/Delete Modules; polish Index; Wysiwyg; nuevas deps de color fuera del canon
  (usar input nativo / shadcn existentes).

## Decisions

### D1 — Payload traducible: objeto por campo, no `label_es`
**Elección:** Inertia envía `label: { es, ca, en }` y `explanation: { … }` (Spatie
JSON). LangTabs escribe en esas claves por locale activo.
**Alternativa descartada:** wire estilo v1 `label_es` en el request (obligaría a
remapear y choca con `HasTranslations`).

### D2 — Slug solo en servidor
**Elección:** `CreateModule` (o FormRequest `prepareForValidation` + Action) calcula
`slug = Str::slug($name)` y valida unique CI; el Form Create no incluye el campo.
**Alternativa descartada:** preview de slug readonly en UI (no pedido).

### D3 — Unique case-insensitive
**Elección:** reglas `unique` con `whereRaw('LOWER(name) = ?', [strtolower($name)])`
(o equivalente Rule::unique()->where) sobre `modules` (soft deletes: respetar
`SoftDeletes` / `withoutTrashed` según convención existente). Mismo para slug.
**Alternativa descartada:** collation DB only (menos portable / no explícito en app).

### D4 — LangTabs API
**Elección:** `LangTabs` recibe `locales` (default: `usePage().props.supportedLocales`)
y `children(locale)` o slots por campo; labels de tab vía i18n (`idioma_es`, etc. o
mapa existente del LanguageSwitcher).
**Alternativa descartada:** tres formularios duplicados hardcodeados es/ca/en.

### D5 — IconPicker: catálogo curado en código
**Elección:** lista cerrada en `lib/menuIcons.ts` (o `lib/erpIcons.ts`) — ampliar
`ICON_MAP` / exportar `ERP_ICON_NAMES` (~25–40 Lucide: settings, users, building,
package, truck, file-text, calculator, warehouse, clipboard-list, …). Grid en
`Dialog` shadcn. Valor = key string.
**Alternativa descartada:** importar todos los iconos Lucide (ruido + emoticonos).

### D6 — ColorPicker sin paquete nuevo
**Elección:** `input type="color"` (+ texto hex opcional) envuelto en Form/ColorPicker
+ Field. Sin añadir deps a `libraries.md`.
**Alternativa descartada:** react-colorful / similar (no está en canon).

### D7 — Status = Switch del kit
**Elección:** mapear checked ↔ `active`/`inactive`; default `active` en estado inicial
del form Create.
**Alternativa descartada:** select (MVP actual).

### D8 — Create page dedicada vs ModuleForm compartido
**Elección:** Create usa el nuevo formulario (kit completo). Edit **no** se migra en
este change (puede conservar ModuleForm antiguo o un stub); prohibido “arreglar Edit”
de paso.
**Alternativa descartada:** un solo form Create/Edit ya perfecto (amplía alcance).

### D9 — Standards
**Elección:** actualizar fila form-kit en `frontend.md` (LangTabs dejado de pendiente;
añadir ColorPicker e IconPicker). Sin excepciones a standards.

## Risks / Trade-offs

- [Riesgo] Soft-deleted module con mismo name/slug bloquea recreación. → Mitigación:
  seguir convención Spatie SoftDeletes del proyecto en unique (documentar en task;
  si restore es preferible, fuera de alcance salvo test rojo).
- [Riesgo] Catálogo de iconos incompleto para módulos futuros. → Mitigación: lista
  ampliable en un solo fichero; 20 decide el set inicial ERP-oriented.
- [Trade-off] Edit queda desalineado hasta su change. → Aceptado (Luis: solo Create).

## Migration Plan

1. Implementar kit → Create UI → StoreModuleRequest/CreateModule → tests.
2. Sin migración BD salvo índice unique CI si se decide (opcional; no requerido si
   validación app basta para Fase 0).
3. Rollback: revertir change.

## Open Questions

Ninguna bloqueante.
