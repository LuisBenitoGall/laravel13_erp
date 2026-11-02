# Proposal

## Why

El Create de Module es un MVP incompleto (slug editable, label/status toscos, icono/color
como texto) y no cumple el formulario acordado con Luis ni el patrón de idiomas del
proyecto. Hace falta el alta real alineada a v1 (mejorada) y el `LangTabs` reutilizable
del form-kit, pendiente desde el MVP.

## What Changes

- Añadir **LangTabs** al form-kit: pestañas dinámicas por idiomas activos del runtime;
  primer consumidor = Create de Module (label text + explanation textarea por locale).
- Rehacer el formulario **Create** de Module: nombre, nivel (select), icono (modal +
  grid Lucide curado ERP), color (colorpicker), status (toggle, default Active),
  LangTabs; **sin** input de slug.
- Backend Create: slug auto desde nombre; `name` y `slug` únicos **case-insensitive**;
  traducciones — solo locale principal (`es`) required para label/explanation; resto
  opcional; sigue creando `{slug}.access`.
- Tras guardar: redirect a Edit (el controller ya lo hace; mantener/contratar).
- Controles reutilizables de color e icono en el kit (o bajo Form) para no dejar
  primitivas ad-hoc solo en Modules.
- **BREAKING (contrato Create UI/API de entrada):** el cliente ya no envía `slug`; el
  servidor lo deriva. Validación de `label`/`explanation` pasa a “principal required”.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `form-kit`: incorporar `LangTabs` (antes excluido del MVP); documentar ColorPicker e
  IconPicker (modal/grid) como controles del kit usados por Create.
- `admin-module-catalog`: contrato de alta de Module (slug auto + inmutable, unicidad
  CI de name/slug, status default Active, label/explanation por locale con principal
  required, permiso `{slug}.access`, redirect Edit).
- `admin-ui-login`: alinear mención del catálogo UI / Create de Module con el formulario
  completo (si el escenario de listado/kit lo implica; delta mínimo).

## Impact

- Front: `Components/Form` (LangTabs, ColorPicker, IconPicker),
  `Admin/Modules/Create` / `ModuleForm` (separar Create del Edit MVP),
  `menuIcons` / catálogo curado Lucide, i18n UI.
- Backend: `StoreModuleRequest`, `CreateModule` (slug desde name), posibles índices/
  reglas unique CI; tests Feature de store.
- Specs: `form-kit`, `admin-module-catalog`, `admin-ui-login`.
- Standards: actualizar catálogo en `frontend.md` (LangTabs + nuevos controles) en el
  mismo change.
- Fuera: Edit/Delete Modules, polish Index, Wysiwyg, E2E §4 (N/A — no es flujo crítico
  listado).
