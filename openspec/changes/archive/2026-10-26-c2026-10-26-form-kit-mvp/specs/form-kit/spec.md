## ADDED Requirements

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda sobre opciones locales), `DatePicker`,
`Checkbox` y `Switch` integrados con react-hook-form y zod, con label/error/help
homogéneos vía `Field`. SHALL NOT añadir Wysiwyg, Dropzone ni LangTabs en este change.
SHALL documentar un ejemplo de uso en el template frontend del proyecto. SHALL migrar
al menos un formulario existente de referencia al kit (sin migrar el resto del ERP).

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa en un formulario RHF con error de validación
- **THEN** se muestran label y mensaje de error de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo con el kit
- **THEN** puede componer Field con Input, Textarea, NumberInput, Select, DatePicker,
  Checkbox y Switch sin inventar primitivas ad-hoc para esos tipos

#### Scenario: Select filtra opciones locales
- **WHEN** el usuario escribe en el Select del kit con un conjunto de opciones locales
- **THEN** la lista visible se filtra por el texto introducido

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override de maxLength
- **THEN** aplica el default del estándar vigente (255 / R-FE-08)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** no se han añadido Wysiwyg, Dropzone ni LangTabs como parte del MVP

#### Scenario: Formulario de referencia migrado
- **WHEN** se completa el change
- **THEN** al menos un formulario existente del ERP usa el kit (migración mínima de
  referencia)
