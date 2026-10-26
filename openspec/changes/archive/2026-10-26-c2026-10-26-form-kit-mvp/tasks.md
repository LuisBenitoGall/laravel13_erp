## 1. Primativas ui + Form kit

- [x] 1.1 Añadir primativas shadcn/`ui` necesarias (label, textarea, checkbox, switch,
      popover, etc.) sin romper `ui/input` R-FE-08
- [x] 1.2 `Form/Field` (label, error, help) + wrappers RHF
- [x] 1.3 `Form/Input` (envuelve ui/input), `Textarea`, `NumberInput` (prop decimals)
- [x] 1.4 `Form/Select` combobox: opciones locales + filtro texto; API opcional onSearch
      sin endpoint
- [x] 1.5 `Form/DatePicker` (input date + date-fns; instalar date-fns si falta en npm)
- [x] 1.6 `Form/Checkbox` y `Form/Switch`

## 2. Docs y referencia

- [x] 2.1 Ejemplo de uso en `docs/standards/templates-frontend.md`
- [x] 2.2 Nota en `frontend.md` §4: MVP implementado; Wysiwyg/Dropzone/LangTabs pendientes
- [x] 2.3 Migrar `CompanyForm` (u otro form único acordado en design) al kit

## 3. Calidad

- [x] 3.1 `npm run typecheck` + lint en verde; arch maxLength sigue pasando
- [x] 3.2 E2E: N/A
- [x] 3.3 Sin añadir Vitest/RTL ni paquetes fuera de canon (salvo date-fns ya listado)
