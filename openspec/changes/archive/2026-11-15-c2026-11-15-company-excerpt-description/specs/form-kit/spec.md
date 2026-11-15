# Spec Delta

## ADDED Requirements

### Requirement: Wysiwyg en el form-kit
El sistema SHALL exponer `Form/Wysiwyg` reutilizable basado en
`@tiptap/react`, controlado por valor HTML string, integrable con `Field`,
con toolbar mínima de formato (negrita, cursiva, listas, headings). SHALL NOT
acoplarse a Company.

#### Scenario: Valor controlado
- **WHEN** un consumidor pasa `value` HTML y `onChange`
- **THEN** el editor refleja el valor y emite HTML al editar

#### Scenario: Uso con Field
- **WHEN** Wysiwyg se usa dentro de Field con error de validación
- **THEN** label y error se muestran de forma homogénea al resto del kit

## MODIFIED Requirements

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda sobre opciones locales), `DatePicker`,
`Checkbox`, `Switch`, `LangTabs`, `ColorPicker`, `IconPicker`, `Dropzone` y
`Wysiwyg` integrados con el kit (RHF/zod o espejo Inertia vía Field donde aplique), con
label/error/help homogéneos vía `Field` cuando corresponda. SHALL documentar uso en el
template frontend y mantener al menos un formulario de referencia en el kit.

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa en un formulario RHF con error de validación
- **THEN** se muestran label y mensaje de error de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo con el kit
- **THEN** puede componer Field con Input, Textarea, NumberInput, Select, DatePicker,
  Checkbox, Switch, LangTabs, ColorPicker, IconPicker, Dropzone y Wysiwyg sin inventar
  primitivas ad-hoc para esos tipos

#### Scenario: Select filtra opciones locales
- **WHEN** el usuario escribe en el Select del kit con un conjunto de opciones locales
- **THEN** la lista visible se filtra por el texto introducido

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override de maxLength
- **THEN** aplica el default del estándar vigente (255 / R-FE-08)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** Wysiwyg forma parte del kit Form (deja de estar excluido)

#### Scenario: Formulario de referencia migrado
- **WHEN** se completa el change
- **THEN** al menos un formulario existente del ERP usa el kit (migración mínima de
  referencia; Create de Module cuenta)
