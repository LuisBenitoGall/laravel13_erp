## Why

Fase 0 exige kit de formularios del catálogo (`frontend.md` §4). Hoy solo hay
`ui/input` (+ maxLength); los forms de dominio usan markup ad-hoc. Sin Field +
controles RHF/zod, Fase 1 (maestros) repetirá deuda. Es el change B del cierre de
restos de Fase 0.

## What Changes

- Kit MVP en `Components/Form/`: `Field`, `Input` (alineado R-FE-08), `Textarea`,
  `NumberInput`, `Select` (combobox con búsqueda local; hook remoto opcional),
  `DatePicker`, `Checkbox`, `Switch` — integrados con react-hook-form + zod.
- Primitivas shadcn/`ui/*` necesarias (Label, Checkbox, Switch, Popover, etc.)
  según el patrón ya permitido en `libraries.md`.
- Documentación de uso en `templates-frontend.md` (y nota en `frontend.md` si hace
  falta).
- Migración mínima de **un** formulario existente como referencia (p. ej. CompanyForm
  o Auth login) — no migrar todas las pantallas.
- **No** Wysiwyg, Dropzone, LangTabs, FormActions, RecordEditLayout, LineItemsEditor.
- **No** consola Platform (change C).

## Capabilities

### New Capabilities

- `form-kit`: contrato del kit Form MVP (componentes, integración RHF/zod, alcance
  MVP y exclusiones).

### Modified Capabilities

- (ninguna spec de dominio backend)

## Impact

- `resources/js/Components/Form/*`, posiblemente `Components/ui/*` adicionales
- `docs/standards/templates-frontend.md` (+ `frontend.md` / `libraries.md` solo si
  hace falta dependencia nueva justificada)
- Un form de referencia migrado
- `npm run typecheck` / lint; E2E N/A
