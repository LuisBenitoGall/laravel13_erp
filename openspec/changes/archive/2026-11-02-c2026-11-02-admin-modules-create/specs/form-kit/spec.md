# Spec Delta

## ADDED Requirements

### Requirement: LangTabs en el form-kit
El sistema SHALL exponer `Form/LangTabs`: pestañas de idioma generadas dinámicamente
desde los idiomas activos del runtime (`supportedLocales`). El consumidor SHALL
declarar el contenido por locale (render prop / children). SHALL integrar errores de
validación por locale cuando el formulario use RHF/zod o el espejo Inertia.

#### Scenario: Tabs según idiomas activos
- **WHEN** un formulario usa LangTabs y el runtime expone N idiomas activos
- **THEN** se renderizan N pestañas, una por idioma, con etiqueta de idioma

#### Scenario: Contenido por pestaña
- **WHEN** el usuario cambia de pestaña en LangTabs
- **THEN** ve los campos traducibles declarados para ese locale (mismo layout)

### Requirement: ColorPicker en el form-kit
El sistema SHALL exponer un `Form/ColorPicker` reutilizable (sin dependencia nueva fuera
del canon) usable con Field, para elegir un color obligatorio en formularios.

#### Scenario: ColorPicker selecciona color
- **WHEN** el usuario elige un color en ColorPicker
- **THEN** el valor del campo es un color usable en CSS (p. ej. hex) y Field muestra
  label/error homogéneos

### Requirement: IconPicker en el form-kit
El sistema SHALL exponer un `Form/IconPicker` reutilizable: control que abre un modal
con grid de iconos Lucide de un catálogo curado (orientado a ERP; sin emoticonos). El
valor persistido SHALL ser el nombre Lucide seleccionado.

#### Scenario: Selección desde modal grid
- **WHEN** el usuario abre IconPicker y elige un icono del grid
- **THEN** el valor del campo es el nombre Lucide de ese icono y el control refleja la
  selección

#### Scenario: Catálogo sin emoticonos
- **WHEN** se muestra el grid del IconPicker
- **THEN** solo aparecen iconos del catálogo curado ERP (sin emoticonos / iconos
  frívolos)

## MODIFIED Requirements

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda sobre opciones locales), `DatePicker`,
`Checkbox`, `Switch`, `LangTabs`, `ColorPicker` e `IconPicker` integrados con el kit
(RHF/zod o espejo Inertia vía Field), con label/error/help homogéneos vía `Field`.
SHALL NOT añadir Wysiwyg ni Dropzone en este change. SHALL documentar uso en el
template frontend y mantener al menos un formulario de referencia en el kit.

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa en un formulario RHF con error de validación
- **THEN** se muestran label y mensaje de error de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo con el kit
- **THEN** puede componer Field con Input, Textarea, NumberInput, Select, DatePicker,
  Checkbox, Switch, LangTabs, ColorPicker e IconPicker sin inventar primitivas ad-hoc
  para esos tipos

#### Scenario: Select filtra opciones locales
- **WHEN** el usuario escribe en el Select del kit con un conjunto de opciones locales
- **THEN** la lista visible se filtra por el texto introducido

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override de maxLength
- **THEN** aplica el default del estándar vigente (255 / R-FE-08)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** no se han añadido Wysiwyg ni Dropzone como parte del entregable

#### Scenario: Formulario de referencia migrado
- **WHEN** se completa el change
- **THEN** al menos un formulario existente del ERP usa el kit (migración mínima de
  referencia; Create de Module cuenta)
