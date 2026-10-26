<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-26-form-kit-mvp
Rule: 10-architecture
Summary: Segundo change del cierre de Fase 0: materializar el kit de formularios MVP del
catálogo frontend (`Form/Field` + controles básicos) integrado con react-hook-form + zod,
sin Wysiwyg/Dropzone/LangTabs. Deja el shell listo para maestros (Fase 1).
Goals:
- Implementar en `Components/Form/` el contrato del catálogo (§4) para el MVP:
  `Field` (label/error/help homogéneos) + `Input` (reutilizar/alinear R-FE-08 maxLength),
  `Textarea`, `NumberInput` (decimales por contexto), `Select` (combobox con búsqueda),
  `DatePicker`, `Checkbox`, `Switch` — todos usables con RHF + zod (`libraries.md`).
- Documentar uso en `frontend.md` / templates-frontend si hace falta el “cómo se usa”
  (el catálogo ya nombra el kit).
- Dejar el patrón listo para formularios futuros (Fase 1); no exigir migrar todas las
  pantallas existentes en este change (10 delimita si hay una migración mínima de
  referencia o solo componentes + ejemplo).
NonGoals:
- `Wysiwyg`, `Dropzone`, `LangTabs` (aplazados; no entran en este MVP).
- `FormActions`, `RecordEditLayout`, `ActionBar`, `LineItemsEditor`, `ConfirmDialog`
  (otros ítems del catálogo; fuera de este change salvo que Field los requiera de forma
  mínima — 10 no los engorda).
- Consola Platform MVP (change C siguiente).
- Nuevos módulos de negocio / maestros Fase 1.
Skills:
- frontend-design
- laravel-patterns
- tailwind-css-patterns
Notes:
- Cierre Fase 0: (A) company-role-assignment archivado → (B) este change → (C)
  platform-console-mvp.
- Canon: `docs/standards/frontend.md` §4 fila `Form/Field` + kit; R-FE-05 i18n; R-FE-08
  maxLength en Input.
- Dependencias: solo `libraries.md`. Si DatePicker/Select exigen paquete no listado, 10
  justifica y añade al canon en el mismo change (o resuelve con shadcn/radix ya permitido).
- Select del catálogo menciona “búsqueda remota”; el MVP MUST tener búsqueda en opciones;
  el cableado remoto concreto lo delimita 10 (local-first + API opcional vs stub).
- Stack: Inertia v3 + React 19 + TS strict + Tailwind 4 + shadcn.
- Tests: lo que 10 considere (RTL/Vitest si está en canon, o smoke build + arch); E2E N/A
  salvo que toque flujo de `e2e.md` §4 (no debería).


## ADDED Requirements

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda), `DatePicker`, `Checkbox` y `Switch`
bajo `resources/js/Components/Form/` (o estructura equivalente del catálogo), integrados
con react-hook-form + zod, con label/error/help homogéneos vía `Field`. SHALL NOT incluir
en este change Wysiwyg, Dropzone ni LangTabs.

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa dentro de un formulario RHF
- **THEN** muestra label, mensaje de error de validación y help de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo
- **THEN** puede componer Field + Input/Textarea/NumberInput/Select/DatePicker/Checkbox/Switch
  sin crear primitivas ad-hoc para esos tipos

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override
- **THEN** aplica el default de maxLength del estándar vigente (R-FE-08 / 255)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** no se han añadido Wysiwyg, Dropzone ni LangTabs como parte del MVP


## MODIFIED Requirements

No aplica a specs de dominio backend; el cambio es de catálogo frontend / tooling de UI.
Si 10 abre una capability `form-kit` nueva en OpenSpec, no modifica company-roles ni
platform-roles.
