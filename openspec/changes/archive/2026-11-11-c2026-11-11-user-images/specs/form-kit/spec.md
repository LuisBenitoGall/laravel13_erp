## MODIFIED Requirements

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda sobre opciones locales), `DatePicker`,
`Checkbox`, `Switch`, `LangTabs`, `ColorPicker`, `IconPicker` y `Dropzone`
integrados con el kit (RHF/zod o espejo Inertia vía Field donde aplique), con
label/error/help homogéneos vía `Field` cuando corresponda. SHALL NOT añadir
Wysiwyg en este change. SHALL documentar uso en el template frontend y mantener
al menos un formulario de referencia en el kit.

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa en un formulario RHF con error de validación
- **THEN** se muestran label y mensaje de error de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo con el kit
- **THEN** puede componer Field con Input, Textarea, NumberInput, Select, DatePicker,
  Checkbox, Switch, LangTabs, ColorPicker, IconPicker y Dropzone sin inventar
  primitivas ad-hoc para esos tipos

#### Scenario: Select filtra opciones locales
- **WHEN** el usuario escribe en el Select del kit con un conjunto de opciones locales
- **THEN** la lista visible se filtra por el texto introducido

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override de maxLength
- **THEN** aplica el default del estándar vigente (255 / R-FE-08)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** no se ha añadido Wysiwyg como parte del entregable

#### Scenario: Formulario de referencia migrado
- **WHEN** se completa el change
- **THEN** al menos un formulario existente del ERP usa el kit (migración mínima de
  referencia; Create de Module cuenta)

## ADDED Requirements

### Requirement: Dropzone en el form-kit
El sistema SHALL exponer `Form/Dropzone` reutilizable (basado en
`react-dropzone`), configurable al menos por formatos admitidos (`accept`) y
tamaño máximo (`maxSize`), y opcionalmente `maxFiles` / `multiple`. SHALL NOT
acoplarse a `UserImage`.

#### Scenario: Configuración por props
- **WHEN** un consumidor pasa accept y maxSize distintos
- **THEN** el Dropzone aplica esas restricciones en cliente

#### Scenario: Rechazo cliente
- **WHEN** el usuario selecciona un fichero fuera de accept o maxSize
- **THEN** se informa del error sin subir ese fichero
